<?php

/*
* This file is part of the Akeneo PIM Enterprise Edition.
*
* (c) 2015 Akeneo SAS (http://www.akeneo.com)
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

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
