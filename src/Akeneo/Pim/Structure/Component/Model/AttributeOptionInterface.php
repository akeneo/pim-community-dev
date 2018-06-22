<?php

namespace Akeneo\Pim\Structure\Component\Model;

use Akeneo\Tool\Component\StorageUtils\Model\ReferableInterface;

/**
 * Attribute options
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
interface AttributeOptionInterface extends ReferableInterface
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
     * @return AttributeOptionInterface
     */
    public function setId($id);

    /**
     * Get attribute
     *
     * @return AttributeInterface
     */
    public function getAttribute();

    /**
     * Set attribute
     *
     * @param AttributeInterface $attribute
     *
     * @return AttributeOptionInterface
     */
    public function setAttribute(AttributeInterface $attribute = null);

    /**
     * Get values
     *
     * @return \ArrayAccess
     */
    public function getOptionValues();

    /**
     * Get used locale
     *
     * @return string $locale
     */
    public function getLocale();

    /**
     * Set used locale
     *
     * @param string $locale
     *
     * @return AttributeOptionInterface
     */
    public function setLocale($locale);

    /**
     * Set sort order
     *
     * @param string $sortOrder
     *
     * @return AttributeOptionInterface
     */
    public function setSortOrder($sortOrder);

    /**
     * Get sort order
     *
     * @return int
     */
    public function getSortOrder();

    /**
     * Set code
     *
     * @param string $code
     *
     * @return AttributeOptionInterface
     */
    public function setCode($code);

    /**
     * Get code
     *
     * @return string
     */
    public function getCode();

    /**
     * Returns the current translation
     *
     * @return AttributeOptionValueInterface
     */
    public function getTranslation();

    /**
     * Add option value
     *
     * @param AttributeOptionValueInterface $value
     *
     * @return AttributeOptionInterface
     */
    public function addOptionValue(AttributeOptionValueInterface $value);

    /**
     * Remove value
     *
     * @param AttributeOptionValueInterface $value
     *
     * @return AttributeOptionInterface
     */
    public function removeOptionValue(AttributeOptionValueInterface $value);

    /**
     * Get localized value
     *
     * @return AttributeOptionValueInterface
     */
    public function getOptionValue();

    /**
     * @return string
     */
    public function __toString();
}
