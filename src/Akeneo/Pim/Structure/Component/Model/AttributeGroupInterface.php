<?php

namespace Akeneo\Pim\Structure\Component\Model;

use Akeneo\Tool\Component\Localization\Model\TranslatableInterface;
use Akeneo\Tool\Component\StorageUtils\Model\ReferableInterface;
use Akeneo\Tool\Component\Versioning\Model\TimestampableInterface;
use Akeneo\Tool\Component\Versioning\Model\VersionableInterface;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Attribute Group interface
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface AttributeGroupInterface extends
    TimestampableInterface,
    TranslatableInterface,
    ReferableInterface,
    VersionableInterface
{
    /**
     * Get id
     */
    public function getId(): int;

    /**
     * Set id
     *
     * @param int $id
     */
    public function setId(int $id);

    /**
     * Get code
     */
    public function getCode(): string;

    /**
     * Set code
     *
     * @param string $code
     */
    public function setCode(string $code);

    /**
     * Get sort order
     */
    public function getSortOrder(): int;

    /**
     * Set sort order
     *
     * @param string $sortOrder
     */
    public function setSortOrder(string $sortOrder);

    /**
     * Get created
     */
    public function getCreated(): \DateTime;

    /**
     * Set created datetime
     *
     * @param \DateTime $created
     */
    public function setCreated(\DateTime $created);

    /**
     * Get updated datetime
     */
    public function getUpdated(): \DateTime;

    /**
     * Set updated datetime
     *
     * @param \DateTime $updated
     */
    public function setUpdated(\DateTime $updated);

    /**
     * Add attributes
     *
     * @param AttributeInterface $attribute
     */
    public function addAttribute(AttributeInterface $attribute);

    /**
     * Remove attributes
     *
     * @param AttributeInterface $attribute
     */
    public function removeAttribute(AttributeInterface $attribute);

    /**
     * Get attributes
     */
    public function getAttributes(): ArrayCollection;

    /**
     * Check if the group has an attribute
     *
     * @param AttributeInterface $attribute
     */
    public function hasAttribute(AttributeInterface $attribute): bool;

    public function getMaxAttributeSortOrder(): int;

    /**
     * {@inheritdoc}
     */
    public function setLocale(?string $locale);

    /**
     * Get label
     */
    public function getLabel(): string;

    /**
     * Set label
     *
     * @param string $label
     */
    public function setLabel(string $label);

    /**
     * Returns the label of the attribute group
     *
     * @return string
     */
    public function __toString();
}
