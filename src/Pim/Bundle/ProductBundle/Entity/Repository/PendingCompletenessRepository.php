<?php

namespace Pim\Bundle\ProductBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

/**
 *
 * Enter description here ...
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class PendingCompletenessRepository extends EntityRepository
{
    /**
     * Find a collection where field type is not null
     * Allow to find all pending completeness
     * @param string $fieldType
     * @throws \Exception
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function findWith($fieldType)
    {
        $expectedFields = array('channel', 'locale', 'family');
        if (in_array($fieldType, $expectedFields)) {
            $qb = $this->createQueryBuilder('pc');
            $qb->where(
                $qb->expr()->isNotNull(sprintf('pc.%s', $fieldType))
            );

            return $qb->getQuery()->getResult();
        } else {
            throw new \Exception(sprintf('Unexpected field type %s', $fieldType));
        }
    }
}
