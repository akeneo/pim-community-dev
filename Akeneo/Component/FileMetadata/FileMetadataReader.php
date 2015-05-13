<?php

namespace Akeneo\Component\FileMetadata;

use Akeneo\Component\FileMetadata\Adapter\AdapterInterface;

/**
 * File metadata reader implementation.
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 */
class FileMetadataReader implements FileMetadataReaderInterface
{
    /** @var AdapterInterface[] */
    protected $adapters = [];

    /**
     * @param AdapterInterface[] $adapters
     */
    public function __construct(array $adapters)
    {
        $this->adapters = $adapters;
    }

    /**
     * {@inheritdoc}
     */
    public function all(\SplFileInfo $file)
    {
        $metadata = [];
        foreach ($this->adapters as $adapter) {
            $metadata = array_merge($metadata, $adapter->all($file));
        }

        return $metadata;
    }
}
