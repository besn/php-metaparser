<?php

/**
 * projectname: metaverse/feedparser
 *
 * @package metaverse/feedparser
 */

namespace FeedParser;

/**
 * Class FeedItem
 */
class FeedItem extends \FeedParser\FeedBase
{
  /**
   * @param $feed_type
   */
  public function __construct($item_type, \SimpleXMLElement $i)
  {
    $this->time = new \DateTime();
    $this->feed_type = $item_type;
    $this->meta_type = 'item';

    if (count($i->children()) > 0) {
      foreach ($i->children() as $meta_key => $meta_value) {
        $this->processMetaData('', $meta_key, $meta_value);
        unset($meta_key, $meta_value);
      }
    }

    // get the namespaces used in the item
    $namespaces = $i->getNamespaces(true);

    // go through the list of used namespaces
    foreach ($namespaces as $ns => $ns_uri) {
      if (isset(\FeedParser\FeedParser::$plugins[$ns])) {
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
