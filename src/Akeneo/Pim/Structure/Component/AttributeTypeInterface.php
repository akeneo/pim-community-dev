<?php

namespace Akeneo\Pim\Structure\Component;

/**
 * Contract for an attribute type. Defines its name, its backend type and if the related attributes
 * will be unique or not (like SKU for instance).
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface AttributeTypeInterface
{
    /**
     * Get name
     */
    public function getName(): string;

    /**
     * Get backend type
     */
    public function getBackendType(): string;

    /**
     * Is unique
     */
    public function isUnique(): bool;
}
