<?php

namespace Pim\Bundle\FlexibleEntityBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Pim\Bundle\FlexibleEntityBundle\Model\AbstractAttribute;

/**
 * Repository for AttributeOption entity
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeOptionRepository extends EntityRepository
{
    /**
     * Return all attribute options and values that belong to the provided attribute
     *
     * @param AbstractAttribute $attribute
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function findAllForAttributeWithValues(AbstractAttribute $attribute)
    {
        $qb = $this->createQueryBuilder('o')
            ->addSelect('Values')
            ->leftJoin('o.optionValues', 'Values')
            ->where('o.attribute = :attribute')
            ->setParameter('attribute', (int) $attribute->getId());

        return $qb->getQuery()->execute();
    }
}
