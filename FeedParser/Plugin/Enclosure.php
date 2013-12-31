<?php

/**
 * projectname: metaverse/feedparser
 *
 * @package metaverse/feedparser
 */

namespace FeedParser\Plugin;

/**
 * Enclosure Plugin
 */
class Enclosure extends \FeedParser\Plugin\Plugin
{
  private $enclosure_id = null;
  private $enclosure = array();

  private function processData(\FeedParser\Base $feedbase, $meta_key, \SimpleXMLElement $meta_value)
  {
    switch ((string)$meta_key) {
      case 'enclosure':
        $this->enclosure_id = md5($meta_value->asXML());
        $this->enclosure['url'] = (string)$meta_value['url'];
        $this->enclosure['length'] = (string)$meta_value['length'];
        $this->enclosure['type'] = (string)$meta_value['type'];
        break;
    }
  }

  public function applyMetaData(\FeedParser\Base $feedbase)
  {
    $feedbase->attachments[$this->enclosure_id] = $this->enclosure;
  }

  public function processMetaData(\FeedParser\Base $feedbase, $meta_namespace, $meta_key, $meta_value)
  {
    switch ((string)$feedbase->meta_type) {
      case 'item':
        switch ((string)$meta_key) {
          case 'enclosure':
            $this->processData($feedbase, $meta_key, $meta_value);
            break;
        }
        break;
    }
    unset($meta_namespace, $meta_key, $meta_value);
  }
}
