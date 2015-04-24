<?php

namespace Akeneo\DAM\Component\Metadata;

interface MetadataInterface
{
    public function all(\SplFileInfo $file);
    public function has(\SplFileInfo $file, $key);
    public function get(\SplFileInfo $file, $key);
    public function set(\SplFileInfo $file, $key, $value);
}
