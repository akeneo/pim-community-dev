<?php

namespace Akeneo\DAM\Component\Metadata;

use Akeneo\DAM\Component\Metadata\Adapter\AdapterRegistry;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesser;

class MetadataFactory
{
    /** @var AdapterRegistry */
    protected $registry;

    public function __construct(AdapterRegistry $registry)
    {
        $this->registry = $registry;
    }

    public function create(\SplFileInfo $file)
    {
        $adapters = [];

        $mimeTypeGuesser = MimeTypeGuesser::getInstance();
        $mime = $mimeTypeGuesser->guess($file->getPathname());

        foreach ($this->registry->all() as $adapter) {
            if ($adapter->supportsMimeType($mime)) {
                $adapters[] = $adapter;
            }
        }

        return new Metadata($adapters);
    }
}
