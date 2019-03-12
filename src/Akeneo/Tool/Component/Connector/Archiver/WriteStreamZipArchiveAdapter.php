<?php

namespace Akeneo\Tool\Component\Connector\Archiver;

use League\Flysystem\Config;
use League\Flysystem\ZipArchive\ZipArchiveAdapter;

class WriteStreamZipArchiveAdapter extends ZipArchiveAdapter
{
    /**
     * {@inheritdoc}
     */
    public function writeStream($path, $resource, Config $config)
    {
        $location = $this->applyPathPrefix($path);

        $metadata = stream_get_meta_data($resource);
        $uri = $metadata['uri'];

        return $this->archive->addFile($uri, $location);
    }
}
