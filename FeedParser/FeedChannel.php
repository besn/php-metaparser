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
    // define a default feed updated time
    $this->time = new \DateTime();

    $this->feed_type = $this->getFeedType($x);
    $this->meta_type = 'channel';

    $feed = null;
    $items = null;
    $items_key = null;

    switch ($this->feed_type) {
      case FEEDPARSER_TYPE_RSS_20:
        $feed = $x->channel;
        $items = $x->channel->item;
        $items_key = 'item';
        break;

      case FEEDPARSER_TYPE_ATOM_10:
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
      if (isset(\FeedParser\FeedParser::$plugins[$ns])) {
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

    /*
        foreach($namespaces as $ns => $ns_uri) {
          syslog(LOG_DEBUG, var_export($ns, true));
          syslog(LOG_DEBUG, var_export($ns_uri, true));

          if (count($feed->children($ns, true)) > 0) {
            foreach ($feed->children($ns, true) as $meta_key => $meta_value) {
              syslog(LOG_DEBUG, sprintf('%s = %s', $meta_key, $meta_value));
              unset($meta_key, $meta_value);
            }
          }
        }
    */

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
    // extract channel data
    switch ($x->getName()) {
      case 'rss':
        switch (isset($x['version'])) {
          case '2.0':
            return FEEDPARSER_TYPE_RSS_20;
            break;
        }
        break;

      case 'feed':
        return FEEDPARSER_TYPE_ATOM_10;
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
