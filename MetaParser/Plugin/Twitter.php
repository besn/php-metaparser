<?php

namespace MetaParser\Plugin;

use MetaParser\MetaParser;

/**
 * Twitter Cards Plugin
 */
class Twitter extends Plugin
{
  /** @var string $title Title of content (max 70 characters) */
  private $title = null;
  /** @var string $description Description of content (maximum 200 characters) */
  private $description = null;
  /** @var string $url */
  private $url = null;
  /** @var array $images The images from twitter:image* */
  private $images = [];

  public function __construct()
  {
    $this->namespace = 'twitter';
    $this->prefix = $this->namespace . ':';
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
      case 'url':
        $this->url = (string)$meta_value;
        break;
      case 'image':
      case 'image:src':
        $this->images['twitter:image' . ((count($this->images) > 0) ? abs(count($this->images) + 1) : '')] = (string)$meta_value;
        break;

      case 'image0':
      case 'image1':
      case 'image2':
      case 'image3':
        $this->images['twitter:' . (string)$meta_key] = (string)$meta_value;
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
    if (count($this->images) > 0)
    {
      $parser->meta['images'] = array_merge(isset($parser->meta['images']) && is_array($parser->meta['images']) ? $parser->meta['images'] : [], $this->images);
    }
  }

  public function processMetaData(MetaParser $parser, $meta_namespace, $meta_key, $meta_value)
  {
    switch ((string)$meta_namespace)
    {
      case 'twitter':
        $this->processData($parser, $meta_key, $meta_value);
        break;
    }
  }
}
