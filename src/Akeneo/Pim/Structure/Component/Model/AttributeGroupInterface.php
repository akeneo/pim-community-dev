<?php

namespace Akeneo\Pim\Structure\Component\Model;

use Akeneo\Tool\Component\Localization\Model\TranslatableInterface;
use Akeneo\Tool\Component\StorageUtils\Model\ReferableInterface;
use Akeneo\Tool\Component\Versioning\Model\VersionableInterface;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Attribute Group interface
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface AttributeGroupInterface extends TranslatableInterface, ReferableInterface, VersionableInterface
{
    /**
     * Get id
     *
     * @return int
     */
    public function getId();

    /**
     * Set id
     *
     * @param int $id
     *
     * @return AttributeGroupInterface
     */
    public function setId($id);

    /**
     * Get code
     *
     * @return string
     */
    public function getCode();

    /**
     * Set code
     *
     * @param string $code
     *
     * @return AttributeGroupInterface
     */
    public function setCode($code);

    /**
     * Get sort order
     *
     * @return int
     */
    public function getSortOrder();

    /**
     * Set sort order
     *
     * @param string $sortOrder
     *
     * @return AttributeGroupInterface
     */
    public function setSortOrder($sortOrder);

    /**
     * Get created
     *
     * @return \DateTime
     */
    public function getCreated();

    /**
     * Set created datetime
     *
     * @param \DateTime $created
     *
     * @return AttributeGroupInterface
     */
    public function setCreated($created);

    /**
     * Get updated datetime
     *
     * @return \DateTime
     */
    public function getUpdated();

    /**
     * Set updated datetime
     *
     * @param \DateTime $updated
     *
     * @return AttributeGroupInterface
     */
    public function setUpdated($updated);

    /**
     * Add attributes
     *
     * @param AttributeInterface $attribute
     *
     * @return AttributeGroupInterface
     */
    public function addAttribute(AttributeInterface $attribute);

    /**
     * Remove attributes
     *
     * @param AttributeInterface $attribute
     *
     * @return AttributeGroupInterface
     */
    public function removeAttribute(AttributeInterface $attribute);

    /**
     * Get attributes
     *
     * @return ArrayCollection
     */
    public function getAttributes();

    /**
     * Check if the group has an attribute
     *
     * @param AttributeInterface $attribute
     *
     * @return bool
     */
    public function hasAttribute(AttributeInterface $attribute);

    /**
     * @return int
     */
    public function getMaxAttributeSortOrder();

    /**
     * {@inheritdoc}
     */
    public function setLocale($locale);

    /**
     * Get label
     *
     * @return string
     */
    public function getLabel();

    /**
     * Set label
     *
     * @param string $label
     *
     * @return AttributeGroupInterface
     */
    public function setLabel($label);

    /**
     * Returns the label of the attribute group
     *
     * @return string
     */
    public function __toString();
}
