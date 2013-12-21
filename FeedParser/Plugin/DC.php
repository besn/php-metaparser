<?php

/**
 * projectname: metaverse/feedparser
 *
 * @package metaverse/feedparser
 */

namespace FeedParser\Plugin;

/**
 * FeedParser Dublin Core Plugin
 *
 * The Dublin Core namespace allows for meta data to be associated with content.
 *
 * @source http://www.feedforall.com/dublin-core.htm
 */
class DC extends \FeedParser\Plugin\Plugin
{
  private function processData(\FeedParser\FeedBase $feedbase, $meta_key, $meta_value)
  {
    switch ((string)$meta_key) {
      case 'creator': // The primary individual responsible for the content of the resource.
        $feedbase->author = (string)$meta_value;
        break;
      case 'title': // Title by which the resource is known.
        $feedbase->title = (string)$meta_value;
        break;
    }
  }

  public function processMetaData(\FeedParser\FeedBase $feedbase, $meta_namespace, $meta_key, $meta_value)
  {
    switch ((string)$feedbase->meta_type) {
      case 'channel':
        switch ((string)$meta_namespace) {
          case 'dc':
            $this->processData($feedbase, $meta_key, $meta_value);
            break;
        }
        break;

      case 'item':
        switch ((string)$meta_namespace) {
          case 'dc':
            $this->processData($feedbase, $meta_key, $meta_value);
            break;
        }
        break;
    }
    unset($meta_namespace, $meta_key, $meta_value);
  }
}
