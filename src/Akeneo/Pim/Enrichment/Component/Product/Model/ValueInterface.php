<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Model;

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
     */
    public function getAttributeCode(): string;

    /**
     * Get used locale code
     */
    public function getLocaleCode(): ?string;

    /**
     * Check if the value data is linked to a locale
     */
    public function isLocalizable(): bool;

    /**
     * Check if the value contains data
     */
    public function hasData(): bool;

    /**
     * Get used scope
     */
    public function getScopeCode(): ?string;

    /**
     * Check if the value data is linked to a scope
     */
    public function isScopable(): bool;

    /**
     * Checks that the product value is equal to another.
     */
    public function isEqual(ValueInterface $value): bool;


    public function __toString(): string;
}
