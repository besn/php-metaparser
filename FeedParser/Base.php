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
  public $feed_type = null;
  public $meta_type = null;
  public $title = null;
  public $link = null;
  public $description = null;
  public $author = null;
  public $time = null;
  public $enclosures = array();
  public $media = array();
}
