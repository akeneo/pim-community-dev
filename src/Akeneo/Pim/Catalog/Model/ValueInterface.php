<?php

namespace Pim\Component\Catalog\Model;

/**
 * Product value interface
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ValueInterface
{
    /**
     * Get data
     *
     * @return mixed
     */
    public function getData();

    /**
     * Get attribute
     *
     * @return AttributeInterface
     */
    public function getAttribute();

    /**
     * Get used locale
     *
     * @return string
     */
    public function getLocale();

    /**
     * Check if the value contains data
     *
     * @return bool
     */
    public function hasData();

    /**
     * Get used scope
     *
     * @return string $scope
     */
    public function getScope();

    /**
     * Checks that the product value is equal to another.
     *
     * @param ValueInterface $value
     *
     * @return bool
     */
    public function isEqual(ValueInterface $value);

    /**
     * @return string
     */
    public function __toString();
}
