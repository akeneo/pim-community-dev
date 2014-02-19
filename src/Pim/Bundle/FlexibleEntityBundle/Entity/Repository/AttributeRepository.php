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
     * @return \Pim\Bundle\FlexibleEntityBundle\Model\AbstractAttribute
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
     * Get attribute as array indexed by code
     *
     * @param string  $entityType the entity type
     * @param boolean $withLabel  translated label should be joined
     * @param string  $locale     the locale code of the label
     * @param mixed   $ids        the attribute ids
     *
     * @return array
     */
    public function getAttributesAsArray($entityType, $withLabel = false, $locale = null, $ids = null)
    {
        $qb = $this->_em->createQueryBuilder()
            ->select('att')
            ->from($this->_entityName, 'att', 'att.code')
            ->where('att.entityType = :entityType')->setParameter('entityType', $entityType);
        if ($ids !== null) {
            $qb->andWhere('att.id IN (:ids)')->setParameter('ids', $ids);
        }
        $results = $qb->getQuery()->execute(array(), AbstractQuery::HYDRATE_ARRAY);

        if ($withLabel) {
            $labelExpr = 'COALESCE(trans.label, CONCAT(\'[\', att.code, \']\'))';
            $qb = $this->_em->createQueryBuilder()
                ->select('att.code', sprintf('%s as label', $labelExpr))
                ->from($this->_entityName, 'att', 'att.code')
                ->leftJoin('att.translations', 'trans', 'WITH', 'trans.locale = :locale')->setParameter('locale', $locale)
                ->where('att.entityType = :entityType')->setParameter('entityType', $entityType);
            if ($ids !== null) {
                $qb->andWhere('att.id IN (:ids)')->setParameter('ids', $ids);
            }
            $labels = $qb->getQuery()->execute(array(), AbstractQuery::HYDRATE_ARRAY);
            foreach ($labels as $code => $data) {
                $results[$code]['label']= $data['label'];
            }
        }

        return $results;
    }

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

    /**
     * Get ids of attributes useable in grid
     *
     * @param string  $entityType the entity type
     * @param boolean $asColumn   the attribute is useable as column
     * @param bolean  $asFilter   the attribute is useable as filter
     *
     * @return array
     */
    public function getAttributeIdsUseableInGrid($entityType, $asColumn = true, $asFilter = true)
    {
        $qb = $this->_em->createQueryBuilder()
            ->select('att.id')
            ->from($this->_entityName, 'att', 'att.id')
            ->where('att.entityType = :entityType');

        if ($asColumn && $asFilter) {
            $qb->andWhere('att.useableAsGridColumn = 1 OR att.useableAsGridFilter = 1');
        } elseif ($asColumn) {
            $qb->andWhere('att.useableAsGridColumn = 1');
        } elseif ($asFilter) {
            $qb->andWhere('att.useableAsGridFilter = 1');
        }

        $parameters = ['entityType' => $entityType];
        $result = $qb->getQuery()->execute($parameters, AbstractQuery::HYDRATE_ARRAY);

        return array_keys($result);
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
            unset(self::$attributesCache);
        } else {
            $cacheId = self::getAttributesListCacheId($entityType);
            if (isset(self::$attributesCache[$cacheId])) {
                unset(self::$attributesCache[$cacheId]);
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
