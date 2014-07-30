<?php

namespace Pim\Bundle\CatalogBundle\Model;

use Symfony\Component\HttpFoundation\File\File;

/**
 * Abstract product media (backend type entity)
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractProductMedia
{
    /**
     * @var integer $id
     */
    protected $id;

    /**
     * File uploaded in form
     *
     * @var \Symfony\Component\HttpFoundation\File\File $file
     */
    protected $file;

    /**
     * Filename
     *
     * @var string $filename
     */
    protected $filename;

    /**
     * File path
     *
     * @var string $filePath
     */
    protected $filePath;

    /**
     * Original file name
     *
     * @var string $originalFilename
     */
    protected $originalFilename;

    /**
     * Mime type
     *
     * @var string $mimeType
     */
    protected $mimeType;

    /**
     * @var AbstractProductValue
     */
    protected $value;

    /**
     * @var boolean
     */
    protected $removed = false;

    /**
     * @var integer
     */
    protected $copyFrom;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set id
     *
     * @param integer $id
     *
     * @return AbstractProductMedia
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get file
     *
     * @return \Symfony\Component\HttpFoundation\File\File
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Set file
     *
     * @param \Symfony\Component\HttpFoundation\File\File $file
     *
     * @return AbstractProductMedia
     */
    public function setFile(File $file)
    {
        $this->file = $file;

        return $this;
    }

    /**
     * Get filename
     *
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Set filename
     *
     * @param string $filename
     *
     * @return AbstractProductMedia
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;

        return $this;
    }

    /**
     * Get file path
     *
     * @return string
     */
    public function getFilePath()
    {
        return $this->filePath;
    }

    /**
     * Set file path
     *
     * @param string $filePath
     *
     * @return AbstractProductMedia
     */
    public function setFilePath($filePath)
    {
        $this->filePath = $filePath;

        return $this;
    }

    /**
     * Get original filename
     *
     * @return string
     */
    public function getOriginalFilename()
    {
        return $this->originalFilename;
    }

    /**
     * Set original filename
     *
     * @param string $originalFilename
     *
     * @return AbstractProductMedia
     */
    public function setOriginalFilename($originalFilename)
    {
        $this->originalFilename = $originalFilename;

        return $this;
    }

    /**
     * Get mime type
     *
     * @return string
     */
    public function getMimeType()
    {
        return $this->mimeType;
    }

    /**
     * Set mime type
     *
     * @param string $mimeType
     *
     * @return AbstractProductMedia
     */
    public function setMimeType($mimeType)
    {
        $this->mimeType = $mimeType;

        return $this;
    }

    /**
     * @param boolean $removed
     *
     * @return AbstractProductMedia
     */
    public function setRemoved($removed)
    {
        $this->removed = $removed;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isRemoved()
    {
        return $this->removed;
    }

    /**
     * Set the product value
     *
     * @param ProductValueInterface $value
     *
     * @return AbstractProductMedia
     */
    public function setValue(ProductValueInterface $value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get the product value
     *
     * @return ProductValueInterface
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Get the media id to copy from
     *
     * @return int
     */
    public function getCopyFrom()
    {
        return $this->copyFrom;
    }

    /**
     * Set the media id to copy from
     *
     * @param integer $copyFrom
     *
     * @return AbstractProductMedia
     */
    public function setCopyFrom($copyFrom)
    {
        $this->copyFrom = $copyFrom;

        return $this;
    }

    /**
     * To string
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->filename;
    }

    /**
     * Reset the media file
     *
     * @return AbstractProductMedia
     */
    public function resetFile()
    {
        $this->file = null;

        return $this;
    }
}
