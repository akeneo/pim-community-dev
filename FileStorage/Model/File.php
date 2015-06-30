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
 * File
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 */
class File implements FileInterface
{
    /** @var int */
    protected $id;

    /** @var string */
    protected $key;

    /** @var string */
    protected $guid;

    /** @var string */
    protected $originalFilename;

    /** @var string */
    protected $mimeType;

    /** @var int */
    protected $size;

    /** @var string */
    protected $extension;

    /** @var string */
    protected $storage;

    //TODDO: check if we really need it
    /** @var UploadedFile */
    protected $uploadedFile;

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getGuid()
    {
        return $this->guid;
    }

    /**
     * {@inheritdoc}
     */
    public function setGuid($guid)
    {
        $this->guid = $guid;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getOriginalFilename()
    {
        return $this->originalFilename;
    }

    /**
     * {@inheritdoc}
     */
    public function setOriginalFilename($originalFilename)
    {
        $this->originalFilename = $originalFilename;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getMimeType()
    {
        return $this->mimeType;
    }

    /**
     * {@inheritdoc}
     */
    public function setMimeType($mimeType)
    {
        $this->mimeType = $mimeType;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * {@inheritdoc}
     */
    public function setSize($size)
    {
        $this->size = $size;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getExtension()
    {
        return $this->extension;
    }

    /**
     * {@inheritdoc}
     */
    public function setExtension($extension)
    {
        $this->extension = $extension;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getStorage()
    {
        return $this->storage;
    }

    /**
     * {@inheritdoc}
     */
    public function setStorage($storage)
    {
        $this->storage = $storage;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * {@inheritdoc}
     */
    public function setKey($key)
    {
        $this->key = $key;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getUploadedFile()
    {
        return $this->uploadedFile;
    }

    /**
     * {@inheritdoc}
     */
    public function setUploadedFile(UploadedFile $uploadedFile = null)
    {
        $this->uploadedFile = $uploadedFile;

        return $this;
    }
}
