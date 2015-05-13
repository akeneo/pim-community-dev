<?php

namespace Akeneo\Component\FileMetadata;

/**
 * File metadata reader interface.
 * Extracts all available metatada of a file.
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 */
interface FileMetadataReaderInterface
{
    /**
     * Read all metadata for the given $file.
     *
     * @param \SplFileInfo $file
     *
     * @return array
     */
    public function all(\SplFileInfo $file);
}
