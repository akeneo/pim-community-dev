<?php

namespace Pim\Bundle\CatalogBundle\Factory;

use Pim\Bundle\CatalogBundle\Model\AttributeOptionInterface;

/**
 * Attribute Option factory
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeOptionFactory
{
    /** @var string */
    protected $objectClass;

    /**
     * @param string $objectClass
     */
    public function __construct($objectClass)
    {
        $this->objectClass = $objectClass;
    }

    /**
     * Create an attribute option
     *
     * @return AttributeOptionInterface
     */
    public function createAttributeOption()
    {
        return new $this->objectClass();
    }
}
