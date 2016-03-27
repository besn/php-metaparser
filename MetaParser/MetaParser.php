<?php

/**
 * projectname: metaverse/metaparser
 *
 * @package metaverse/metaparser
 */

namespace MetaParser;

use DOMDocument;
use DOMXPath;
use Exception;
use MetaParser\Plugin\Plugin;
use SimpleXMLElement;

/**
 * Parses the meta data of a HTML file
 *
 * @param $feed_content
 * @return array
 * @throws Exception
 */
class MetaParser
{
  public $plugins = [];
  public $meta = [];

  public function __construct()
  {
    // define the plugins
    if (is_dir(dirname(__FILE__) . '/Plugin/'))
    {
      foreach (glob(dirname(__FILE__) . '/Plugin/*.php') as $plugin_file)
      {
        $plugin_class = str_replace('.php', '', basename($plugin_file));
        $this->plugins[strtolower($plugin_class)] = 'MetaParser\Plugin\\' . $plugin_class;
      }
    }
  }

  /**
   * Parses a HTML file and returns an array with \MetaParser\Meta and \MetaParser\Link objects
   *
   * @param string $html_file
   * @return array[Meta]
   * @throws Exception
   */
  public function parse($html_file)
  {
    if (!isset($html_file))
    {
      throw new Exception('missing html file');
    }

    // reset the meta data
    $this->meta = [];

    // parse the html content
    $doc = new DOMDocument();
    libxml_use_internal_errors(true);
    if (!($doc->loadHTML($html_file)))
    {
      throw new Exception('error loading html');
    }
    libxml_clear_errors();
    if (!($x = simplexml_import_dom($doc)))
    {
      throw new Exception('error parsing html');
    }

    // extract <meta> and <link> items and the page title
    $this->extractTagData($x);
    $this->extractPageTitle($doc);

    // process the meta data
    $this->processMetaData();

    // sort the meta data
    ksort($this->meta);

    return $this->meta;
  }

  /**
   * Extract the <meta> and <link> tags
   *
   * @param SimpleXMLElement $x
   */
  private function extractTagData(SimpleXMLElement $x)
  {
    $nodes = $x->xpath('/html/head/meta');
    foreach ($nodes as $node)
    {
      // extract the meta data of this tag
      $this->extractMetaData($node);

      // check if this element has siblings
      $siblings = $node->xpath('sibling::*');
      if (is_array($siblings))
      {
        echo "count: " . count($siblings) . "\n";
        foreach ($siblings as $sibling)
        {
          // extract the meta data of this sibling tag
          $this->extractMetaData($sibling);
        }
      }
    }
    unset ($nodes);

    $nodes = $x->xpath('/html/head/link');
    foreach ($nodes as $node)
    {
      switch ($node['rel'])
      {
        case 'canonical':
        case 'shorturl':
          $this->meta['urls'][(string)$node['rel']] = trim((string)$node['href']);
          break;
      }
    }
    unset ($nodes);
  }

  /**
   * Extract the content of the <meta> tags
   *
   * @param $node SimpleXMLElement
   */
  private function extractMetaData($node)
  {
    // check if we have name and content attributes
    if (isset($node['name']) && isset($node['content']))
    {
      $this->meta[trim((string)$node['name'])] = trim((string)$node['content']);
    }
    // check if we have property and content attributes
    if (isset($node['property']) && isset($node['content']))
    {
      $this->meta[trim((string)$node['property'])] = trim((string)$node['content']);
    }
  }

  /**
   * Extract the content of the <title> tag of a page
   *
   * @param $doc
   */
  private function extractPageTitle($doc)
  {
    $xpath = new DOMXPath($doc);
    $titleNode = $xpath->query('//title');
    if (!empty($titleNode->item(0)->nodeValue))
    {
      $this->meta['titles']['page_title'] = trim($titleNode->item(0)->nodeValue);
    }
  }

  /**
   * Process the meta data
   */
  private function processMetaData()
  {
    // initialize the plugins
    $p = array();
    foreach ($this->plugins as $plugin_name => $plugin_class)
    {
      $p[$plugin_name] = new $plugin_class;
    }

    // process the meta data
    foreach ($this->meta as $meta_key => $meta_value)
    {
      /** @var $meta_plugin Plugin */
      $meta_plugin = $p['core'];
      $meta_namespace = '';
      if (isset($p[$meta_key]) && $p[$meta_key] instanceof Plugin)
      {
        $meta_plugin = $p[$meta_key];
      }
      foreach ($p as $plugin_name => $plugin_class)
      {
        /** @var $plugin_class Plugin */
        if (!is_null($plugin_class->prefix) && strpos($meta_key, $plugin_class->prefix) === 0)
        {
          $meta_plugin = $plugin_class;
          $meta_namespace = $plugin_class->namespace;
          $meta_key = strtolower(substr($meta_key, strlen($plugin_class->prefix)));
        }
      }

      $meta_plugin->processMetaData($this, $meta_namespace, $meta_key, $meta_value);
    }

    // apply the meta data
    foreach ($p as $plugin_name => $plugin_class)
    {
      $plugin_class->applyMetaData($this);
    }
  }

  /**
   * Returns the title from the meta data
   *
   * @param array $prefered_titles The prefered kind(s) of title in the order of preference (default: 'twitter:title', 'og:title', 'DC.title', 'page_title', ...)
   * @return string|null The title
   * @throws Exception
   */
  public function getTitle($prefered_titles = [])
  {
    if (is_array($prefered_titles))
    {
      if (count($prefered_titles) === 0)
      {
        $prefered_titles = ['twitter:title', 'og:title', 'DC.title', 'page_title'];
      }
      foreach ($prefered_titles as $prefered_title)
      {
        switch ($prefered_title)
        {
          case 'meta_title':
          case 'page_title':
          case 'twitter:title':
          case 'og:title':
          case 'DC.title':
            if (isset($this->meta[$prefered_title]))
            {
              return $this->meta[$prefered_title];
            }
            break;

          default:
            throw new Exception('Unknown title "' . $prefered_title . '"');
            break;
        }
      }
    }

    return null;
  }

  /**
   * Returns the description from the meta data
   *
   * @param array $prefered_descriptions The prefered kind(s) of description in the order of preference (default: 'twitter:description', 'og:description', 'DC.description', 'meta_description', ...)
   * @return string|null The description
   * @throws Exception
   */
  public function getDescription($prefered_descriptions = [])
  {
    if (is_array($prefered_descriptions))
    {
      if (count($prefered_descriptions) === 0)
      {
        $prefered_descriptions = ['twitter:description', 'og:description', 'DC.description', 'meta_description'];
      }
      foreach ($prefered_descriptions as $prefered_description)
      {
        switch ($prefered_description)
        {
          case 'meta_description':
          case 'twitter:description':
          case 'og:description':
          case 'DC.description':
            if (isset($this->meta[$prefered_description]))
            {
              return $this->meta[$prefered_description];
            }
            break;

          default:
            throw new Exception('Unknown description "' . $prefered_description . '"');
            break;
        }
      }
    }

    return null;
  }

  /**
   * Returns the canonial url from the meta data
   *
   * @param array $prefered_urls The prefered kind(s) of url in the order of preference (default: 'canonical', 'twitter:url', 'og:url', 'DC.identifier', 'shorturl', ...)
   * @return string|null The url
   * @throws Exception
   */
  public function getUrl($prefered_urls = [])
  {
    if (is_array($prefered_urls))
    {
      if (count($prefered_urls) === 0)
      {
        $prefered_urls = ['canonical', 'twitter:url', 'og:url', 'DC.identifier', 'shorturl'];
      }
      foreach ($prefered_urls as $prefered_url)
      {
        switch ($prefered_url)
        {
          case 'twitter:url':
          case 'og:url':
          case 'DC.identifier':
            if (isset($this->meta[$prefered_url]))
            {
              return $this->meta[$prefered_url];
            }
            break;

          case 'canonical':
          case 'shorturl':
            if (isset($this->meta['urls'][$prefered_url]))
            {
              return $this->meta['urls'][$prefered_url];
            }
            break;

          default:
            throw new Exception('Unknown url "' . $prefered_url . '"');
            break;
        }
      }
    }

    return null;
  }

  /**
   * Returns the keywords from the meta data
   *
   * @return array The keywords
   */
  public function getKeywords()
  {
    return (isset($this->meta['keywords']) && is_array($this->meta['keywords'])) ? $this->meta['keywords'] : [];
  }

  /**
   * Returns the images from the meta data
   *
   * @param array $prefered_images The prefered kind(s) of image in the order of preference (default: 'twitter:image', 'og:image', 'image0', 'image1', 'image2', 'image3', ...)
   * @return string|null The image
   * @throws Exception
   */
  public function getImages($prefered_images = [])
  {
    if (is_array($prefered_images))
    {
      if (count($prefered_images) === 0)
      {
        $prefered_images = ['twitter:image', 'og:image', 'image0', 'image1', 'image2', 'image3'];
      }
      foreach ($prefered_images as $prefered_image)
      {
        switch ($prefered_image)
        {
          case 'twitter:image':
          case 'og:image':
          case 'image0':
          case 'image1':
          case 'image2':
          case 'image3':
            if (isset($this->meta[$prefered_image]))
            {
              return $this->meta[$prefered_image];
            }
            break;

          default:
            throw new Exception('Unknown image "' . $prefered_image . '"');
            break;
        }
      }
    }

    return null;
  }
}
