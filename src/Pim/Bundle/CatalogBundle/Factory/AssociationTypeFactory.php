<?php

namespace Pim\Bundle\CatalogBundle\Factory;

/**
 * Association type factory
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AssociationTypeFactory
{
    /** @var string */
    protected $associationTypeClass;

    /**
     * @param string $associationTypeClass
     */
    public function __construct($associationTypeClass)
    {
        $this->associationTypeClass = $associationTypeClass;
    }

    /**
     * Creates a association type instance
     *
     * @return \Pim\Bundle\CatalogBundle\Model\AssociationTypeInterface
     */
    public function createAssociationType()
    {
        return new $this->associationTypeClass();
    }
}
