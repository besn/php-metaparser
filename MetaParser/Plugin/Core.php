<?php

namespace MetaParser\Plugin;

use MetaParser\MetaParser;

/**
 * Core Plugin
 */
class Core extends Plugin
{
  private $description = null;
  private $keywords = [];

  public function processMetaData(MetaParser $parser, $meta_namespace, $meta_key, $meta_value)
  {
    switch (strtolower((string)$meta_key))
    {
      case 'description':
        $this->description = (string)$meta_value;
        break;
      case 'keywords':
        if (!is_array($meta_value))
        {
          $this->keywords = preg_split('/\s?,\s?/', (string)$meta_value);
        }
        break;
    }
  }

  public function applyMetaData(MetaParser $parser)
  {
    if (!empty($this->description))
    {
      $parser->meta['descriptions']['meta_description'] = $this->description;
    }
    if (count($this->keywords) > 0)
    {
      $parser->meta['keywords'] = array_merge(isset($parser->meta['keywords']) && is_array($parser->meta['keywords']) ? $parser->meta['keywords'] : [], $this->keywords);
    }
  }
}
