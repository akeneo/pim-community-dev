<?php

namespace Pim\Bundle\CatalogBundle\Entity\Repository;

use Pim\Bundle\FlexibleEntityBundle\Entity\Repository\AttributeOptionRepository as FlexAttributeOptionRepository;
use Pim\Bundle\CatalogBundle\Entity\ProductAttribute;

/**
 * Repository for AttributeOption entity
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeOptionRepository extends FlexAttributeOptionRepository
{
    /**
     * Return query builder for all attribute options that belong to the provided ProductAttribute
     * @param ProductAttribute $attribute
     *
     * @return Doctrine\ORM\QueryBuilder
     */
    public function findAllForAttribute(ProductAttribute $attribute)
    {
        return $this->createQueryBuilder('o')
            ->addSelect('OptionValue')
            ->leftJoin('o.optionValues', 'OptionValue')
            ->where('o.attribute = '.(int) $attribute->getId())
            ->orderBy('o.sortOrder');
    }
}
