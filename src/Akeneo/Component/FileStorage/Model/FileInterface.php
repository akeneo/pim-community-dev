<?php

/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Component\FileStorage\Model;

use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * File interface
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 */
interface FileInterface
{
    /**
     * @return int
     */
    public function getId();

    /**
     * @return string
     */
    public function getKey();

    /**
     * @param string $key
     *
     * @return FileInterface
     */
    public function setKey($key);

    /**
     * @return string
     */
    public function getGuid();

    /**
     * @param string $guid
     *
     * @return FileInterface
     */
    public function setGuid($guid);

    /**
     * @return string
     */
    public function getOriginalFilename();

    /**
     * @param string $originalFilename
     *
     * @return FileInterface
     */
    public function setOriginalFilename($originalFilename);

    /**
     * @return string
     */
    public function getMimeType();

    /**
     * @param string $mimeType
     *
     * @return FileInterface
     */
    public function setMimeType($mimeType);

    /**
     * @return int
     */
    public function getSize();

    /**
     * @param int $size
     *
     * @return FileInterface
     */
    public function setSize($size);

    /**
     * @return string
     */
    public function getExtension();

    /**
     * @param string $extension
     *
     * @return FileInterface
     */
    public function setExtension($extension);

    /**
     * @return string
     */
    public function getStorage();

    /**
     * @param string $storage
     *
     * @return FileInterface
     */
    public function setStorage($storage);

    /**
     * @return UploadedFile
     */
    public function getUploadedFile();

    /**
     * @param UploadedFile $uploadedFile
     *
     * @return FileInterface
     */
    public function setUploadedFile(UploadedFile $uploadedFile);
}
