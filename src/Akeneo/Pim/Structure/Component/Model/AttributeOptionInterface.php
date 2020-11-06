<?php

namespace Akeneo\Pim\Structure\Component\Model;

use Akeneo\Tool\Component\StorageUtils\Model\ReferableInterface;
use Akeneo\Tool\Component\Versioning\Model\VersionableInterface;

/**
 * Attribute options
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
interface AttributeOptionInterface extends ReferableInterface, VersionableInterface
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
    public function setId(int $id): \Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface;

    /**
     * Get attribute
     */
    public function getAttribute(): \Akeneo\Pim\Structure\Component\Model\AttributeInterface;

    /**
     * Set attribute
     *
     * @param AttributeInterface $attribute
     */
    public function setAttribute(AttributeInterface $attribute = null): \Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface;

    /**
     * Get values
     */
    public function getOptionValues(): \ArrayAccess;

    /**
     * Get used locale
     *
     * @return string $locale
     */
    public function getLocale(): string;

    /**
     * Set used locale
     *
     * @param string $locale
     */
    public function setLocale(string $locale): \Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface;

    /**
     * Set sort order
     *
     * @param string $sortOrder
     */
    public function setSortOrder(string $sortOrder): \Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface;

    /**
     * Get sort order
     */
    public function getSortOrder(): int;

    /**
     * Set code
     *
     * @param string $code
     */
    public function setCode(string $code): \Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface;

    /**
     * Get code
     */
    public function getCode(): string;

    /**
     * Returns the current translation
     */
    public function getTranslation(): \Akeneo\Pim\Structure\Component\Model\AttributeOptionValueInterface;

    /**
     * Add option value
     *
     * @param AttributeOptionValueInterface $value
     */
    public function addOptionValue(AttributeOptionValueInterface $value): \Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface;

    /**
     * Remove value
     *
     * @param AttributeOptionValueInterface $value
     */
    public function removeOptionValue(AttributeOptionValueInterface $value): \Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface;

    /**
     * Get localized value
     */
    public function getOptionValue(): \Akeneo\Pim\Structure\Component\Model\AttributeOptionValueInterface;

    /**
     * @return string
     */
    public function __toString();
}
