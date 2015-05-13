<?php

namespace Akeneo\Component\FileMetadata;

/**
 * Factory to create FileMetadataReader for a given file.
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 */
interface FileMetadataReaderFactoryInterface
{
    /**
     * Creates a FileMetadataReader with the given $file.
     *
     * The created FileMetadataReader will have different Adapter(s) depending on the $file
     * it is created for.
     *
     * @param \SplFileInfo $file
     *
     * @return FileMetadataReader
     */
    public function create(\SplFileInfo $file);
}