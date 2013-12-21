<?php

/**
 * projectname: metaverse/feedparser
 *
 * @package metaverse/feedparser
 */

namespace FeedParser;

// Unknown feed type
define('FEEDPARSER_TYPE_UNKNOWN', 0);
// RDF
define('FEEDPARSER_TYPE_RDF', 1);
// RSS 2.0
define('FEEDPARSER_TYPE_RSS', 2);
// ATOM 1.0
define('FEEDPARSER_TYPE_ATOM', 3);

/**
 * Parses a RSS or Atom Feed
 *
 * @param $feed_content
 * @return array
 * @throws \Exception
 */
class FeedParser
{
  public $channel = null;
  public static $plugins = array();

  public function __construct($feed_content)
  {
    if (!isset($feed_content))
      throw new \Exception('missing feed content');

    if (!($x = simplexml_load_string($feed_content)))
      throw new \Exception('error parsing content');

    // initialize plugins
    self::$plugins['dc'] = new \FeedParser\Plugin\DC();
    self::$plugins['sy'] = new \FeedParser\Plugin\SY();
    self::$plugins['content'] = new \FeedParser\Plugin\Content();

    $this->channel = new \FeedParser\Feed($x);
  }
}
