<?php

namespace Pim\Component\Catalog\Factory;

use Pim\Component\Catalog\Model\AttributeGroupInterface;

/**
 * Class AttributeGroupFactory
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeGroupFactory
{
    /** @var string */
    protected $attributeGroupClass;

    /**
     * @param string $attributeGroupClass
     */
    public function __construct($attributeGroupClass)
    {
        $this->attributeGroupClass = $attributeGroupClass;
    }

    /**
     * @return AttributeGroupInterface
     */
    public function create()
    {
        return new $this->attributeGroupClass();
    }
}
