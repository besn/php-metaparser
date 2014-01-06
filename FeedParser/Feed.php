<?php

/**
 * projectname: metaverse/feedparser
 *
 * @package metaverse/feedparser
 */

namespace FeedParser;

/**
 * Class Feed
 */
class Feed extends \FeedParser\Base
{
  public $language = null;
  public $items = array();

  /**
   * @param \SimpleXMLElement $x
   */
  public function __construct(\SimpleXMLElement $x)
  {
    $this->feed_type = $this->getFeedType($x);

    $feed = null;
    $items = null;
    $items_key = null;

    switch ($this->feed_type) {
      case FEEDPARSER_TYPE_RDF:
        $feed = $x->channel;
        $items = $x->item;
        $items_key = 'item';
        break;

      case FEEDPARSER_TYPE_RSS:
        $feed = $x->channel;
        $items = $x->channel->item;
        $items_key = 'item';
        break;

      case FEEDPARSER_TYPE_ATOM:
        $feed = $x;
        $items = $x->entry;
        $items_key = 'entry';
        break;
    }

    // initialize the plugins
    $p = array();
    foreach (\FeedParser\FeedParser::$plugins as $meta_key => $class_name) {
      $p[$meta_key] = new $class_name;
    }

    // extract feed data
    if (count($feed->children()) > 0) {
      foreach ($feed->children() as $meta_key => $meta_value) {
        if ($meta_key != $items_key) {
          if (isset($p[$meta_key]) && $p[$meta_key] instanceof \FeedParser\Plugin\Plugin) {
            $p[$meta_key]->processMetaData($this, '', $meta_key, $meta_value);
          } else {
            $p['core']->processMetaData($this, '', $meta_key, $meta_value);
          }
        }
      }
    }

    // get the namespaces used in the feed
    $namespaces = $x->getNamespaces(true);

    // go through the list of used namespaces
    foreach ($namespaces as $ns => $ns_uri) {
      if (count($feed->children($ns, true)) > 0) {
        foreach ($feed->children($ns, true) as $meta_key => $meta_value) {
          if (isset($p[$ns]) && $p[$ns] instanceof \FeedParser\Plugin\Plugin) {
            $p[$ns]->processMetaData($this, $ns, $meta_key, $meta_value);
          } else {
            $p['core']->processMetaData($this, $ns, $meta_key, $meta_value);
          }
          unset($meta_key, $meta_value);
        }
      }
      unset($ns, $ns_uri);
    }

    // apply the meta data
    foreach (\FeedParser\FeedParser::$plugins as $meta_key => $class_name) {
      $p[$meta_key]->applyMetaData($this);
    }

    // extract item data
    foreach ($items as $i) {
      $this->addItem(new \FeedParser\Item($this->feed_type, $i));
    }

    unset($feed, $items, $items_key);
  }

  /**
   * Tries to find out the type of feed and returns it
   *
   * @param \SimpleXMLElement $x
   */
  public function getFeedType(\SimpleXMLElement $x)
  {
    switch (strtolower($x->getName())) {
      case 'rdf':
        return FEEDPARSER_TYPE_RDF;
        break;

      case 'rss':
        return FEEDPARSER_TYPE_RSS;
        break;

      case 'feed':
        return FEEDPARSER_TYPE_ATOM;
        break;

      default:
        throw new \Exception('unknown feed type');
        break;
    }
  }

  /**
   * @param \FeedParser\Item $item
   */
  public function addItem(\FeedParser\Item $item)
  {
    $this->items[] = $item;
  }

  /**
   * @return array
   */
  public function getItems()
  {
    return $this->items;
  }
}
