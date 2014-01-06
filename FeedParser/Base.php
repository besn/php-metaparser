<?php

/**
 * projectname: metaverse/feedparser
 *
 * @package metaverse/feedparser
 */

namespace FeedParser;

/**
 * Class Base
 */
class Base
{
  /**
   * @var int The type of the feed (0: unknown, 1: rdf, 2: rss, 3: atom)
   */
  public $feed_type = null;
  /**
   * TODO: replace $meta_type with instanceof
   */
  public $meta_type = null;
  /**
   * @var string The title of the feed or item
   */
  public $title = null;
  /**
   * @var string The link to the feed or item
   */
  public $link = null;
  /**
   * @var string The description of the feed or item
   */
  public $description = null;
  /**
   * @var string The author of the feed or item
   */
  public $author = null;
  /**
   * @var string The time of publication of the feed or item
   */
  public $time = null;
  /**
   * @var string Enclosed media
   */
  public $enclosures = array();
  /**
   * @var string Enclosed media (MediaRSS)
   */
  public $media = array();
}
