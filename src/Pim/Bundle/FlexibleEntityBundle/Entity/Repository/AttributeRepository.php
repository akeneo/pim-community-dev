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
     * Get attribute as array indexed by code
     *
     * @param string  $entityType the entity type
     * @param boolean $withLabel  translated label should be joined
     * @param string  $locale     the locale code of the label
     * @param array   $ids        the attribute ids
     *
     * @return array
     */
    public function getAttributesAsArray($entityType, $withLabel = false, $locale = null, array $ids = [])
    {
        $qb = $this->_em->createQueryBuilder()
            ->select('att')
            ->from($this->_entityName, 'att', 'att.code')
            ->where('att.entityType = :entityType')->setParameter('entityType', $entityType);
        if (!empty($ids)) {
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
            if (!empty($ids)) {
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
     * @param string $entityType the entity type
     *
     * @return array
     */
    public function getAttributeIdsUseableInGrid($entityType)
    {
        $qb = $this->_em->createQueryBuilder()
            ->select('att.id')
            ->from($this->_entityName, 'att', 'att.id')
            ->where('att.entityType = :entityType');

        $qb->andWhere(
            "att.useableAsGridColumn = 1 ".
            "OR att.useableAsGridFilter = 1 ".
            "OR att.attributeType = 'pim_catalog_simpleselect'"
        );
        $parameters = ['entityType' => $entityType];
        $result = $qb->getQuery()->execute($parameters, AbstractQuery::HYDRATE_ARRAY);

        return array_keys($result);
    }
}
