<?php

namespace MetaParser\Plugin;

use MetaParser\MetaParser;

/**
 * Dublin Core Plugin
 */
class DC extends Plugin
{
  private $title = null;
  private $description = null;
  private $author = null;
  private $url = null;

  public function __construct()
  {
    $this->namespace = 'DC';
    $this->prefix = $this->namespace . '.';
  }

  public function processData(MetaParser $parser, $meta_key, $meta_value)
  {
    switch ((string)$meta_key)
    {
      case 'title':
        $this->title = (string)$meta_value;
        break;
      case 'description':
        $this->description = (string)$meta_value;
        break;
      case 'creator':
        $this->author = (string)$meta_value;
        break;
      case 'identifier':
        $this->url = (string)$meta_value;
        break;
    }
  }

  public function applyMetaData(MetaParser $parser)
  {
    if (!empty($this->title))
    {
      $parser->meta['titles'][$this->namespace] = $this->title;
    }
    if (!empty($this->description))
    {
      $parser->meta['descriptions'][$this->namespace] = $this->description;
    }
    if (!empty($this->url))
    {
      $parser->meta['urls'][$this->namespace] = $this->url;
    }
  }

  public function processMetaData(MetaParser $parser, $meta_namespace, $meta_key, $meta_value)
  {
    switch ((string)$meta_namespace)
    {
      case 'DC':
        $this->processData($parser, $meta_key, $meta_value);
        break;
    }
  }
}
