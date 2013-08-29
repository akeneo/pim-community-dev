<?php

namespace Pim\Bundle\ProductBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Pending completeness repository
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PendingCompletenessRepository extends EntityRepository
{
    /**
     * Find a collection where field is not null (channel, locale or family)
     * Allow to find all pending completeness
     *
     * @param string $fieldType
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function findByNotNull($fieldName)
    {
        $field = sprintf('pc.%s', $fieldName);

        $qb = $this->createQueryBuilder('pc');
        $qb->where($qb->expr()->isNotNull($field));

        return $qb->getQuery()->getResult();
    }
}
