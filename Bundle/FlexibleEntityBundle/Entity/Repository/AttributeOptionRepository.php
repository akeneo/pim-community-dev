<?php

namespace Oro\Bundle\FlexibleEntityBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttribute;

/**
 * Repository for AttributeOption entity
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
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
