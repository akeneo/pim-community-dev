<?php

namespace Pim\Bundle\ProductBundle\Model;

/**
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AvailableProductAttributes
{
    protected $attributes;

    protected $availableAttributes;

    public function __construct(array $attributes)
    {
        foreach ($attributes as $attribute) {
            $this->attributes[$attribute->getId()]          = false;
            $this->availableAttributes[$attribute->getId()] = $attribute;
        }
    }

    public function getAttributes()
    {
        return $this->attributes;
    }

    public function setAttributes(array $attributes)
    {
        $this->attributes = $attributes;
    }

    public function getAttribute($id)
    {
        return isset($this->availableAttributes[$id]) ? $this->availableAttributes[$id] : null;
    }

    public function getAttributesToAdd()
    {
        $attributesToAdd = array();

        foreach ($this->attributes as $id => $toAdd) {
            if ($toAdd) {
                $attributesToAdd[] = $this->getAttribute($id);
            }
        }

        return $attributesToAdd;
    }
}
