<?php

namespace MetaParser\Plugin;

use MetaParser\MetaParser;

/**
 * Google Plugin
 */
class Google extends Plugin
{
  private $news_keywords = [];

  public function processMetaData(MetaParser $parser, $meta_namespace, $meta_key, $meta_value)
  {
    switch ((string)$meta_key)
    {
      case 'news_keywords':
        $this->news_keywords = preg_split('/\s?,\s?/', (string)$meta_value);
        break;
    }
  }

  public function applyMetaData(MetaParser $parser)
  {
    if (count($this->news_keywords) > 0)
    {
      $parser->meta['keywords'] = array_merge(isset($parser->meta['keywords']) && is_array($parser->meta['keywords']) ? $parser->meta['keywords'] : [], $this->news_keywords);
    }
  }
}
