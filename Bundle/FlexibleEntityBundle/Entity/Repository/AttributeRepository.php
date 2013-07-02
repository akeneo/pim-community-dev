<?php
namespace Oro\Bundle\FlexibleEntityBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\AbstractQuery;

/**
 * Attribute repository
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
class AttributeRepository extends EntityRepository
{
    /**
     * Get associative array of code to attribute
     *
     * @param string $entityType
     *
     * @return array
     */
    public function getCodeToAttributes($entityType)
    {
        $qb = $this->_em->createQueryBuilder()
            ->select('att')->from($this->_entityName, 'att')
            ->where('att.entityType = :entityType')->setParameter('entityType', $entityType);

        // index parameter into from call not works with simple object hydratation
        $result = $qb->getQuery()->execute(array(), AbstractQuery::HYDRATE_SIMPLEOBJECT);
        $associative = array();
        foreach ($result as $row) {
            $associative[$row->getCode()]= $row;
        }

        return $associative;
    }
}
