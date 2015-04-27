<?php

namespace DamEnterprise\Component\Metadata\Adapter;

class IptcAdapter implements AdapterInterface
{
    public function all(\SplFileInfo $file)
    {
        getimagesize($file->getPathname(), $info);
        if (isset($info['APP13'])) {
            return iptcparse($info['APP13']);
        }

        return [];
    }

    public function has(\SplFileInfo $file, $key)
    {
        // TODO: Implement has() method.
    }

    public function get(\SplFileInfo $file, $key)
    {
        // TODO: Implement get() method.
    }

    public function set(\SplFileInfo $file, $key, $value)
    {
        // TODO: Implement set() method.
    }

    public function supports($key)
    {
        // TODO: Implement supports() method.
    }

    public function supportsMimeType($mimeType)
    {
        return in_array($mimeType, [
            'image/jpeg', 'image/tiff',
        ]);
    }
}
