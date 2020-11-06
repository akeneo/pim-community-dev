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
    public function getId(): int;

    /**
     * @param int $id
     */
    public function setId(int $id): \Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;

    /**
     * The key of the file can be either its pathname or a unique identifier.
     */
    public function getKey(): string;

    /**
     * @param string $key
     */
    public function setKey(string $key): \Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;

    public function getOriginalFilename(): string;

    /**
     * @param string $originalFilename
     */
    public function setOriginalFilename(string $originalFilename): \Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;

    public function getMimeType(): string;

    /**
     * @param string $mimeType
     */
    public function setMimeType(string $mimeType): \Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;

    public function getSize(): int;

    /**
     * @param int $size
     */
    public function setSize(int $size): \Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;

    public function getExtension(): string;

    /**
     * @param string $extension
     */
    public function setExtension(string $extension): \Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;

    public function getHash(): string;

    /**
     * @param string $hash
     */
    public function setHash(string $hash): \Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;

    public function getStorage(): string;

    /**
     * @param string $storage
     */
    public function setStorage(string $storage): \Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;

    public function getUploadedFile(): \Symfony\Component\HttpFoundation\File\UploadedFile;

    /**
     * @param UploadedFile $uploadedFile
     */
    public function setUploadedFile(UploadedFile $uploadedFile = null): ?\Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;

    /**
     * @param bool $removed
     */
    public function setRemoved(bool $removed): \Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;

    public function isRemoved(): bool;
}
