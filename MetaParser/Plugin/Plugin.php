<?php

/**
 * projectname: metaverse/metaparser
 *
 * @package metaverse/metaparser
 */

namespace MetaParser\Plugin;

use MetaParser\MetaParser;

class Plugin
{
  public $prefix = null;
  public $namespace = null;

  public function processData(MetaParser $parser, $meta_key, $meta_value)
  {
  }

  public function processMetaData(MetaParser $parser, $meta_namespace, $meta_key, $meta_value)
  {
  }

  public function applyMetaData(MetaParser $parser)
  {
  }
}
