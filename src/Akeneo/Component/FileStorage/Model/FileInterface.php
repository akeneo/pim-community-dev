<?php

namespace Akeneo\Component\FileStorage\Model;

use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * File interface.
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface FileInterface
{
    /**
     * @return int
     */
    public function getId();

    /**
     * The key of the file can be either its pathname or a unique identifier.
     *
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
     * TODO: drop it asap.
     *
     * @return UploadedFile
     */
    public function getUploadedFile();

    /**
     * TODO: drop it asap.
     *
     * @param UploadedFile $uploadedFile
     *
     * @return FileInterface|null
     */
    public function setUploadedFile(UploadedFile $uploadedFile = null);
}
