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
   * Returns the title of the feed or item
   *
   * @return string
   */
  public function getTitle()
  {
    return $this->title;
  }

  /**
   * Returns the author of the feed or item
   *
   * @return string
   */
  public function getAuthor()
  {
    return $this->author;
  }

  /**
   * Returns the description of the feed or item
   *
   * @return string
   */
  public function getDescription()
  {
    return $this->description;
  }

  /**
   * Returns the type of the feed (0: unknown, 1: rdf, 2: rss, 3: atom)
   *
   * @return int
   */
  public function getFeedType()
  {
    return $this->feed_type;
  }

  /**
   * Returns the link to the feed or item
   *
   * @return string
   */
  public function getLink()
  {
    return $this->link;
  }
}
