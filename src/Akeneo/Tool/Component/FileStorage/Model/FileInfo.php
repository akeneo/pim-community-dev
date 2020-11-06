<?php

namespace Akeneo\Tool\Component\FileStorage\Model;

use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * File.
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FileInfo implements FileInfoInterface
{
    /** @var int */
    protected $id;

    /** @var string */
    protected $key;

    /** @var string */
    protected $originalFilename;

    /** @var string */
    protected $mimeType;

    /** @var int */
    protected $size;

    /** @var string */
    protected $extension;

    /** @var string */
    protected $hash;

    /** @var string */
    protected $storage;

    /** @var bool */
    protected $removed = false;

    /** @var UploadedFile */
    protected $uploadedFile;

    /**
     * {@inheritdoc}
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function setId(int $id): FileInfoInterface
    {
        $this->id = $id;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getOriginalFilename(): string
    {
        return $this->originalFilename;
    }

    /**
     * {@inheritdoc}
     */
    public function setOriginalFilename(string $originalFilename): FileInfoInterface
    {
        $this->originalFilename = $originalFilename;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getMimeType(): string
    {
        return $this->mimeType;
    }

    /**
     * {@inheritdoc}
     */
    public function setMimeType(string $mimeType): FileInfoInterface
    {
        $this->mimeType = $mimeType;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getSize(): int
    {
        return $this->size;
    }

    /**
     * {@inheritdoc}
     */
    public function setSize(int $size): FileInfoInterface
    {
        $this->size = $size;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getExtension(): string
    {
        return $this->extension;
    }

    /**
     * {@inheritdoc}
     */
    public function setExtension(string $extension): FileInfoInterface
    {
        $this->extension = $extension;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getHash(): string
    {
        return $this->hash;
    }

    /**
     * {@inheritdoc}
     */
    public function setHash(string $hash): FileInfoInterface
    {
        $this->hash = $hash;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getStorage(): string
    {
        return $this->storage;
    }

    /**
     * {@inheritdoc}
     */
    public function setStorage(string $storage): FileInfoInterface
    {
        $this->storage = $storage;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * {@inheritdoc}
     */
    public function setKey(string $key): FileInfoInterface
    {
        $this->key = $key;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getUploadedFile(): \Symfony\Component\HttpFoundation\File\UploadedFile
    {
        return $this->uploadedFile;
    }

    /**
     * {@inheritdoc}
     */
    public function setUploadedFile(UploadedFile $uploadedFile = null): ?FileInfoInterface
    {
        $this->uploadedFile = $uploadedFile;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setRemoved(bool $removed): FileInfoInterface
    {
        $this->removed = $removed;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isRemoved(): bool
    {
        return $this->removed;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getOriginalFilename();
    }
}
