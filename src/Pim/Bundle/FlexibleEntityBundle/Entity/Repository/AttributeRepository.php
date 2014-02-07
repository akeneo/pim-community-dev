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
     * @var integer
     */
    const CODE_ATTRIBUTES_TTL = 120;

    /**
     * @var string
     */
    const CODE_ATTRIBUTES_PREFIX = 'ATTR_CODE_';

    /**
     * Cache attributes per entity type.
     * Note: this cache will not survive between request, but the
     * used result cache will do, without providing hydration
     *
     * @var array
     */
    protected static $attributesCache;

    /**
     * Get the attribute by code and entity type
     *
     * @param string $entity
     * @param string $code
     *
     * @return Attribute
     */
    public function findOneByEntityAndCode($entity, $code)
    {
        return $this->findOneBy(array('code' => $code, 'entityType' => $entity));
    }

    /**
     * Get associative array of code to attribute
     *
     * @param string $entityType
     *
     * @return array
     */
    public function getCodeToAttributes($entityType)
    {
        $cacheId = self::getAttributesListCacheId($entityType);

        if (!isset(self::$attributesCache[$cacheId])) {
            $qb = $this->_em->createQueryBuilder()
                ->select('att')->from($this->_entityName, 'att')
                ->where('att.entityType = :entityType')->setParameter('entityType', $entityType);

            $query = $qb->getQuery();
            $query->useResultCache(true, null, self::getAttributesListCacheId($entityType));

            $result = $query->execute(array(), AbstractQuery::HYDRATE_SIMPLEOBJECT);
            $associative = array();
            foreach ($result as $row) {
                $associative[$row->getCode()] = $row;
            }

            self::$attributesCache[$cacheId] = $associative;
        }

        return self::$attributesCache[$cacheId];
    }

    /**
     * Clear the attributes cache for the provided entity type
     * or for all entities if no entityType provided
     *
     * @param string $entityType
     */
    public static function clearAttributesCache($entityType = null)
    {
        if (null == $entityType) {
            unset($this->attributesCache);
        } else {
            $cacheId = self::getAttributesListCacheId($entityType);
            if (isset($this->attributesCache[$cacheId])) {
                unset($this->attributesCache[$cacheId]);
            }
        }
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
        return self::CODE_ATTRIBUTES_PREFIX.$entityType;
    }
}
