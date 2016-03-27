<?php

/**
 * projectname: metaverse/metaparser
 *
 * @package metaverse/metaparser
 */

namespace MetaParser\Plugin;

use MetaParser\MetaParser;

/**
 * Open Graph Plugin
 */
class OpenGraph extends Plugin
{
  private $title = null;
  private $description = null;
  private $url = null;
  private $images = [];

  public function __construct()
  {
    $this->namespace = 'og';
    $this->prefix = $this->namespace . ':';
  }

  public function processData(MetaParser $parser, $meta_key, $meta_value)
  {
    switch ((string)$meta_key)
    {
      case 'title':
        $this->title = (string)$meta_value;
        break;
      case 'description':
        $this->description = (string)$meta_value;
        break;
      case 'url':
        $this->url = (string)$meta_value;
        break;
      case 'image':
        $this->images['og:image' . ((count($this->images) > 0) ? abs(count($this->images) + 1) : '')] = (string)$meta_value;
        break;
    }
  }

  public function applyMetaData(MetaParser $parser)
  {
    if (!empty($this->title))
    {
      $parser->meta['titles'][$this->namespace] = $this->title;
    }
    if (!empty($this->description))
    {
      $parser->meta['descriptions'][$this->namespace] = $this->description;
    }
    if (!empty($this->url))
    {
      $parser->meta['urls'][$this->namespace] = $this->url;
    }
    if (count($this->images) > 0)
    {
      $parser->meta['images'] = array_merge(isset($parser->meta['images']) && is_array($parser->meta['images']) ? $parser->meta['images'] : [], $this->images);
    }
  }

  public function processMetaData(MetaParser $parser, $meta_namespace, $meta_key, $meta_value)
  {
    switch ((string)$meta_namespace)
    {
      case 'og':
        $this->processData($parser, $meta_key, $meta_value);
        break;
    }
  }
}
