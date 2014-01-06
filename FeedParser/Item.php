<?php

/**
 * projectname: metaverse/feedparser
 *
 * @package metaverse/feedparser
 */

namespace FeedParser;

/**
 * Class Item
 */
class Item extends \FeedParser\Base
{
  public $categories = array();

  /**
   * @param $feed_type
   */
  public function __construct($item_type, \SimpleXMLElement $item)
  {
    $this->feed_type = $item_type;

    // initialize the plugins
    $p = array();
    foreach (\FeedParser\FeedParser::$plugins as $meta_key => $class_name) {
      $p[$meta_key] = new $class_name;
    }

    if (count($item->children()) > 0) {
      foreach ($item->children() as $meta_key => $meta_value) {
        if (isset($p[$meta_key]) && $p[$meta_key] instanceof \FeedParser\Plugin\Plugin) {
          $p[$meta_key]->processMetaData($this, '', $meta_key, $meta_value);
        } else {
          $p['core']->processMetaData($this, '', $meta_key, $meta_value);
        }
        unset($meta_key, $meta_value);
      }
    }

    // get the namespaces used in the item
    $namespaces = $item->getNamespaces(true);

    // go through the list of used namespaces
    foreach ($namespaces as $ns => $ns_uri) {
      if (count($item->children($ns, true)) > 0) {
        foreach ($item->children($ns, true) as $meta_key => $meta_value) {
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
  }
}
