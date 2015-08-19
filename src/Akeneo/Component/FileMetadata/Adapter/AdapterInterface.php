<?php

/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Component\FileMetadata\Adapter;

/**
 * An Adapter can extract metadata from a file, depending on which
 * mime type it supports.
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 */
interface AdapterInterface
{
    /**
     * Return all metadata this Adapter can read, for the given $file.
     *
     * @param \SplFileInfo $file
     *
     * @return array
     */
    public function all(\SplFileInfo $file);

    /**
     * Return the name of this Adapter.
     *
     * @return string
     */
    public function getName();

    /**
     * Return whether or not the given $mimeType is supported by this Adapter.
     *
     * @param string $mimeType
     *
     * @return bool
     */
    public function isMimeTypeSupported($mimeType);

    /**
     * Return all mime types supported by this Adapter.
     *
     * @return array
     */
    public function getSupportedMimeTypes();
}
