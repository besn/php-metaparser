<?php

/**
 * projectname: metaverse/feedparser
 *
 * @package metaverse/feedparser
 */

namespace FeedParser;

/**
 * Class FeedChannel
 */
class FeedChannel extends \FeedParser\FeedBase
{
  public $items = array();

  /**
   * @param \SimpleXMLElement $x
   */
  public function __construct(\SimpleXMLElement $x)
  {
    $this->feed_type = $this->getFeedType($x);
    $this->meta_type = 'channel';

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

    // extract feed data
    if (count($feed->children()) > 0) {
      foreach ($feed->children() as $meta_key => $meta_value) {
        if ($meta_key != $items_key) {
          $this->processMetaData('', $meta_key, $meta_value);
        }
      }
    }

    // get the namespaces used in the feed
    $namespaces = $x->getNamespaces(true);

    // go through the list of used namespaces
    foreach ($namespaces as $ns => $ns_uri) {
      if (isset(\FeedParser\FeedParser::$plugins[$ns]) && \FeedParser\FeedParser::$plugins[$ns] instanceof \FeedParser\Plugin\Plugin) {
        if (count($feed->children($ns, true)) > 0) {
          foreach ($feed->children($ns, true) as $meta_key => $meta_value) {
            \FeedParser\FeedParser::$plugins[$ns]->processMetaData($this, $ns, $meta_key, $meta_value);
            unset($meta_key, $meta_value);
          }
        }
        \FeedParser\FeedParser::$plugins[$ns]->applyMetaData($this);
      }
      unset($ns, $ns_uri);
    }

    // extract item data
    foreach ($items as $i) {
      $this->addItem(new \FeedParser\FeedItem($this->feed_type, $i));
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
   * @param \FeedParser\FeedItem $item
   */
  public function addItem(\FeedParser\FeedItem $item)
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
