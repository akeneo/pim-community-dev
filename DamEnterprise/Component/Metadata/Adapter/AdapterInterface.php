<?php

namespace DamEnterprise\Component\Metadata\Adapter;

interface AdapterInterface
{
    public function all(\SplFileInfo $file);
    public function getName();
//    public function has(\SplFileInfo $file, $key);
//    public function get(\SplFileInfo $file, $key);
//    public function set(\SplFileInfo $file, $key, $value);
//    public function supports($key);
    public function supportsMimeType($mimeType);
    public function getMimeTypes();
}
