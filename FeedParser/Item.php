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
  /**
   * @param $feed_type
   */
  public function __construct($item_type, \SimpleXMLElement $i)
  {
    $this->feed_type = $item_type;
    $this->meta_type = 'item';

    if (count($i->children()) > 0) {
      foreach ($i->children() as $meta_key => $meta_value) {
        if (isset(\FeedParser\FeedParser::$plugins[$meta_key]) && \FeedParser\FeedParser::$plugins[$meta_key] instanceof \FeedParser\Plugin\Plugin) {
          \FeedParser\FeedParser::$plugins[$meta_key]->processMetaData($this, '', $meta_key, $meta_value);
          \FeedParser\FeedParser::$plugins[$meta_key]->applyMetaData($this);
        } else {
          $this->processMetaData('', $meta_key, $meta_value);
        }
        unset($meta_key, $meta_value);
      }
    }

    // get the namespaces used in the item
    $namespaces = $i->getNamespaces(true);

    // go through the list of used namespaces
    foreach ($namespaces as $ns => $ns_uri) {
      if (isset(\FeedParser\FeedParser::$plugins[$ns]) && \FeedParser\FeedParser::$plugins[$ns] instanceof \FeedParser\Plugin\Plugin) {
        if (count($i->children($ns, true)) > 0) {
          foreach ($i->children($ns, true) as $meta_key => $meta_value) {
            \FeedParser\FeedParser::$plugins[$ns]->processMetaData($this, $ns, $meta_key, $meta_value);
            unset($meta_key, $meta_value);
          }
        }
        \FeedParser\FeedParser::$plugins[$ns]->applyMetaData($this);
      }
      unset($ns, $ns_uri);
    }
  }
}
