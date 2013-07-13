<?php
namespace Pim\Bundle\ProductBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Oro\Bundle\FlexibleEntityBundle\Entity\Repository\AttributeOptionRepository as OroAttributeOptionRepository;
use Pim\Bundle\ProductBundle\Entity\AttributeOption;
use Pim\Bundle\ProductBundle\Entity\ProductAttribute;

/**
 * Repository for AttributeOption entity
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeOptionRepository extends OroAttributeOptionRepository
{
    /**
     * Return query builder for all attribute options that belong to the provided ProductAttribute
     * @param ProductAttribute $attribute
     *
     * @return Doctrine\ORM\QueryBuilder
     */
    public function findAllForAttribute(ProductAttribute $attribute)
    {
        return $this->createQueryBuilder('o')->where('o.attribute = '.(int) $attribute->getId());
    }
}
