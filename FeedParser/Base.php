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

  /**
   * @param $meta_namespace
   * @param $meta_key
   * @param $meta_value
   * @internal param $meta_type
   * @return array|null
   */
  function processMetaData($meta_namespace, $meta_key, $meta_value)
  {
    // syslog(LOG_DEBUG, sprintf('feed: %s, type: %s, ns: %s, key: %s, value: %s', $this->type, $this->meta_type, $meta_namespace, $meta_key, $meta_value));
    switch ((string)$this->meta_type) {
      case 'channel':
        switch ((string)$meta_namespace) {
          case '':
            switch ($this->feed_type) {
              case FEEDPARSER_TYPE_RDF:
                switch (strtolower((string)$meta_key)) {
                  case 'title': // Defines the title of the channel (required)
                  case 'link': // Defines the hyperlink to the channel (required)
                  case 'description': // Describes the channel (required)
                    $this->$meta_key = html_entity_decode((string)$meta_value);
                    break;
                }
                break;

              case FEEDPARSER_TYPE_RSS:
                switch (strtolower((string)$meta_key)) {
                  case 'title': // Defines the title of the channel (required)
                  case 'link': // Defines the hyperlink to the channel (required)
                  case 'description': // Describes the channel (required)
                    $this->$meta_key = html_entity_decode((string)$meta_value);
                    break;

                  case 'pubdate': // Defines the last publication date for the content of the feed (optional)
                    $this->time = new \DateTime((string)$meta_value);
                    $this->time->setTimezone(new \DateTimeZone('UTC'));
                    break;

                  case 'ttl': // Specifies the number of minutes the feed can stay cached before refreshing it from the source (optional)
                    if (is_numeric((int)$meta_value)) {
                      $this->updateFrequency = abs((int)$meta_value * 60);
                    }
                    break;
                }
                break;

              case FEEDPARSER_TYPE_ATOM:
                switch (strtolower((string)$meta_key)) {
                  case 'title': // Defines the title of the channel (required)
                    $this->$meta_key = html_entity_decode((string)$meta_value);
                    break;

                  case 'subtitle': // Describes the channel (required)
                    $this->description = html_entity_decode((string)$meta_value);
                    break;

                  case 'link': // Defines the hyperlink to the channel (required)
                    if (isset($meta_value['rel']) && $meta_value['rel'] == 'alternate' && isset($meta_value['type']) && $meta_value['type'] == 'text/html' && isset($meta_value['href'])) {
                      $this->$meta_key = (string)$meta_value['href'];
                    }
                    break;

                  case 'author': // Specifies the author of the item (optional)
                    if (isset($meta_value->name)) {
                      $this->$meta_key = (string)$meta_value->name;
                    }
                    break;

                  case 'updated': // Defines the last publication date for the content of the feed (optional)
                    $this->time = new \DateTime((string)$meta_value);
                    $this->time->setTimezone(new \DateTimeZone('UTC'));
                    break;
                }
                break;
            }
            break;
        }
        break;

      case 'item':
        switch ((string)$meta_namespace) {
          case '':
            switch ($this->feed_type) {
              case FEEDPARSER_TYPE_RDF:
                switch (strtolower((string)$meta_key)) {
                  case 'title': // Defines the title of the item (required)
                  case 'link': // Defines the hyperlink to the item (required)
                  case 'description': // Describes the item (required)
                    $this->$meta_key = html_entity_decode((string)$meta_value);
                    break;
                }
                break;

              case FEEDPARSER_TYPE_RSS:
                switch (strtolower((string)$meta_key)) {
                  case 'title': // Defines the title of the item (required)
                  case 'link': // Defines the hyperlink to the item (required)
                  case 'description': // Describes the item (required)
                  case 'author': // Specifies the e-mail address to the author of the item (optional)
                    $this->$meta_key = html_entity_decode((string)$meta_value);
                    break;

                  case 'pubdate': // Defines the last publication date for the content of the feed (optional)
                    $this->time = new \DateTime((string)$meta_value);
                    $this->time->setTimezone(new \DateTimeZone('UTC'));
                    break;
                }
                break;

              case FEEDPARSER_TYPE_ATOM:
                switch (strtolower((string)$meta_key)) {
                  case 'title': // Defines the title of the item (required)
                    $this->$meta_key = html_entity_decode((string)$meta_value);
                    break;

                  case 'summary': // Defines the item (required)
                    $this->description = html_entity_decode((string)$meta_value);
                    break;

                  case 'link': // Defines the hyperlink to the item (required)
                    if (isset($meta_value['href'])) {
                      $this->$meta_key = (string)$meta_value['href'];
                    }
                    break;

                  case 'author': // Specifies the author of the item (optional)
                    if (isset($meta_value->name)) {
                      $this->$meta_key = (string)$meta_value->name;
                    }
                    break;

                  case 'updated': // Defines the last publication date for the content of the feed (optional)
                    $this->time = new \DateTime((string)$meta_value);
                    $this->time->setTimezone(new \DateTimeZone('UTC'));
                    break;
                }
                break;
            }
            break;
        }
        break;
    }
    unset($meta_type, $meta_namespace, $meta_key, $meta_value);
  }
}
