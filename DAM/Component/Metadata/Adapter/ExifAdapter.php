<?php

namespace Akeneo\DAM\Component\Metadata\Adapter;

class ExifAdapter implements AdapterInterface
{
    public function all(\SplFileInfo $file)
    {
        return exif_read_data($file->getPathname());
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
