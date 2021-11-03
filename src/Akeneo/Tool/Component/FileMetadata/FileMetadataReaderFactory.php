<?php

/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Akeneo\Tool\Component\FileMetadata;

use Akeneo\Tool\Component\FileMetadata\Adapter\AdapterRegistry;
use Symfony\Component\Mime\MimeTypes;

/**
 * File metadata reader factory implementation.
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 */
class FileMetadataReaderFactory implements FileMetadataReaderFactoryInterface
{
    protected AdapterRegistry $registry;
    protected string $metadataReaderClass;

    /**
     * @param AdapterRegistry $registry
     * @param string          $metadataReaderClass
     */
    public function __construct(
        AdapterRegistry $registry,
        string $metadataReaderClass = 'Akeneo\Tool\Component\FileMetadata\FileMetadataReader'
    ) {
        $this->registry = $registry;
        $this->metadataReaderClass = $metadataReaderClass;
    }

    /**
     * {@inheritdoc}
     */
    public function create(\SplFileInfo $file)
    {
        $adapters = [];

        $mimeType = (new MimeTypes())->guessMimeType($file->getPathname());

        foreach ($this->registry->all() as $adapter) {
            if ($adapter->isMimeTypeSupported($mimeType)) {
                $adapters[] = $adapter;
            }
        }

        return new $this->metadataReaderClass($adapters);
    }
}
