<?php

namespace Pim\Bundle\FlexibleEntityBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\AbstractQuery;

/**
 * Attribute repository
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeRepository extends EntityRepository
{
    /**
     * Get ids from codes
     *
     * @param string $entityType the entity type
     * @param mixed  $codes      the attribute codes
     *
     * @return array
     */
    public function getAttributeIds($entityType, $codes)
    {
        $qb = $this->_em->createQueryBuilder()
            ->select('att.id')
            ->from($this->_entityName, 'att', 'att.id')
            ->where('att.entityType = :entityType')
            ->andWhere('att.code IN (:codes)');

        $parameters = ['entityType' => $entityType, 'codes' => $codes];
        $result = $qb->getQuery()->execute($parameters, AbstractQuery::HYDRATE_ARRAY);

        return array_keys($result);
    }
}
