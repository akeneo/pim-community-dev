<?php

namespace Pim\Bundle\CatalogBundle\Model;

use Symfony\Component\HttpFoundation\File\File;

/**
 * Product media interface (backend type entity)
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ProductMediaInterface
{
    /**
     * Get the product value
     *
     * @return ProductValueInterface
     */
    public function getValue();

    /**
     * Set id
     *
     * @param int|string $id
     *
     * @return ProductMediaInterface
     */
    public function setId($id);

    /**
     * Set file
     *
     * @param File $file
     *
     * @return ProductMediaInterface
     */
    public function setFile(File $file);

    /**
     * @return bool
     */
    public function isRemoved();

    /**
     * Set the product value
     *
     * @param ProductValueInterface $value
     *
     * @return ProductMediaInterface
     */
    public function setValue(ProductValueInterface $value);

    /**
     * Set mime type
     *
     * @param string $mimeType
     *
     * @return ProductMediaInterface
     */
    public function setMimeType($mimeType);

    /**
     * Reset the media file
     *
     * @return ProductMediaInterface
     */
    public function resetFile();

    /**
     * Set the media id to copy from
     *
     * @param int $copyFrom
     *
     * @return ProductMediaInterface
     */
    public function setCopyFrom($copyFrom);

    /**
     * Set original filename
     *
     * @param string $originalFilename
     *
     * @return ProductMediaInterface
     */
    public function setOriginalFilename($originalFilename);

    /**
     * Get original filename
     *
     * @return string
     */
    public function getOriginalFilename();

    /**
     * Get mime type
     *
     * @return string
     */
    public function getMimeType();

    /**
     * Set filename
     *
     * @param string $filename
     *
     * @return ProductMediaInterface
     */
    public function setFilename($filename);

    /**
     * To string
     *
     * @return string
     */
    public function __toString();

    /**
     * Get id
     *
     * @return int|string
     */
    public function getId();

    /**
     * Get filename
     *
     * @return string
     */
    public function getFilename();

    /**
     * Get the media id to copy from
     *
     * @return int
     */
    public function getCopyFrom();

    /**
     * Get file
     *
     * @return File
     */
    public function getFile();

    /**
     * @param bool $removed
     *
     * @return ProductMediaInterface
     */
    public function setRemoved($removed);
}
