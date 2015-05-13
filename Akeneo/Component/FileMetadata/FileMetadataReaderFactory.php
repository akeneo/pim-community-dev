<?php

namespace Akeneo\Component\FileMetadata;

use Akeneo\Component\FileMetadata\Adapter\AdapterRegistry;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesser;

/**
 * File metadata reader factory implementation.
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 */
class FileMetadataReaderFactory implements FileMetadataReaderFactoryInterface
{
    /** @var AdapterRegistry */
    protected $registry;

    /**
     * @param AdapterRegistry $registry
     */
    public function __construct(AdapterRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * {@inheritdoc}
     */
    public function create(\SplFileInfo $file)
    {
        $adapters = [];

        $mimeTypeGuesser = MimeTypeGuesser::getInstance();
        $mimeType = $mimeTypeGuesser->guess($file->getPathname());

        foreach ($this->registry->all() as $adapter) {
            if ($adapter->isMimeTypeSupported($mimeType)) {
                $adapters[] = $adapter;
            }
        }

        return new FileMetadataReader($adapters);
    }
}
