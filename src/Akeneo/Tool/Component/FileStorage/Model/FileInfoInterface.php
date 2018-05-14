<?php

namespace Akeneo\Tool\Component\FileStorage\Model;

use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * File interface.
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface FileInfoInterface
{
    /**
     * @return int
     */
    public function getId();

    /**
     * @param int $id
     *
     * @return FileInfoInterface
     */
    public function setId($id);

    /**
     * The key of the file can be either its pathname or a unique identifier.
     *
     * @return string
     */
    public function getKey();

    /**
     * @param string $key
     *
     * @return FileInfoInterface
     */
    public function setKey($key);

    /**
     * @return string
     */
    public function getOriginalFilename();

    /**
     * @param string $originalFilename
     *
    * @return FileInfoInterface
     */
    public function setOriginalFilename($originalFilename);

    /**
     * @return string
     */
    public function getMimeType();

    /**
     * @param string $mimeType
     *
     *@return FileInfoInterface
     */
    public function setMimeType($mimeType);

    /**
     * @return int
     */
    public function getSize();

    /**
     * @param int $size
     *
     * @return FileInfoInterface
     */
    public function setSize($size);

    /**
     * @return string
     */
    public function getExtension();

    /**
     * @param string $extension
     *
     * @return FileInfoInterface
     */
    public function setExtension($extension);

    /**
     * @return string
     */
    public function getHash();

    /**
     * @param string $hash
     *
     * @return FileInfoInterface
     */
    public function setHash($hash);

    /**
     * @return string
     */
    public function getStorage();

    /**
     * @param string $storage
     *
     * @return FileInfoInterface
     */
    public function setStorage($storage);

    /**
     * @return UploadedFile
     */
    public function getUploadedFile();

    /**
     * @param UploadedFile $uploadedFile
     *
     * @return FileInfoInterface|null
     */
    public function setUploadedFile(UploadedFile $uploadedFile = null);

    /**
     * @param bool $removed
     *
     * @return FileInfoInterface
     */
    public function setRemoved($removed);

    /**
     * @return bool
     */
    public function isRemoved();
}
