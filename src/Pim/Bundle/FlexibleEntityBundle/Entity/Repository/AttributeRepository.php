<?php

namespace Pim\Bundle\FlexibleEntityBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\AbstractQuery;

/**
 * Attribute repository
 */
class AttributeRepository extends EntityRepository
{
    const CODE_ATTRIBUTES_TTL = 120;
    const CODE_ATTRIBUTES_PREFIX = 'ATTR_CODE_';

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

        $query = $qb->getQuery();
        $query->useResultCache(true, null, self::getAttributesListCacheId($entityType));

        // index parameter into from call not works with simple object hydratation
        $result = $query->execute(array(), AbstractQuery::HYDRATE_SIMPLEOBJECT);
        $associative = array();
        foreach ($result as $row) {
            $associative[$row->getCode()]= $row;
        }

        return $associative;
    }

    /**
     * Get the cache id used for the code to attribute list
     * for the entityType provided
     *
     * @param string $entityType
     *
     * @return string cache id
     */
    public static function getAttributesListCacheId($entityType)
    {
        if (is_object($entityType)) {
            echo(get_class($entityType));
        }

        return self::CODE_ATTRIBUTES_PREFIX.$entityType;
    }
}
