<?php

namespace Akeneo\Pim\Structure\Component\Model;

/**
 * Available attributes model
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AvailableAttributes
{
    /** @var array */
    protected $attributes = [];

    /**
     * Set attribute
     *
     * @param array $attributes
     */
    public function setAttributes(array $attributes): void
    {
        $this->attributes = $attributes;
    }

    /**
     * Get attribute
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }
}
