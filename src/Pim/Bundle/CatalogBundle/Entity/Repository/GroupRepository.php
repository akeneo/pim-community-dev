<?php

namespace Pim\Bundle\CatalogBundle\Entity\Repository;

use Pim\Bundle\CatalogBundle\Entity\GroupType;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;

/**
 * Group repository
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupRepository extends ReferableEntityRepository
{
    /**
     * Get ordered groups associative array id to label
     * @param GroupType $type
     *
     * @return array
     */
    public function getChoicesByType(GroupType $type)
    {
        $groups = $this->getGroupsByType($type);

        $choices = [];
        foreach ($groups as $group) {
            $choices[$group->getId()] = $group->getCode();
        }

        return $choices;
    }

    /**
     * Get groups
     *
     * @return array
     */
    public function getChoices()
    {
        $groups = $this
            ->buildAll()
            ->addOrderBy($this->getAlias() .'.code', 'ASC')
            ->getQuery()
            ->getResult();

        $choices = [];
        foreach ($groups as $group) {
            $choices[$group->getId()] = $group->getLabel();
        }

        return $choices;
    }

    /**
     * Return the number of groups containing the provided attribute
     * @param AttributeInterface $attribute
     *
     * @return interger
     */
    public function countForAttribute(AttributeInterface $attribute)
    {
        $qb = $this->createQueryBuilder('g');

        return $qb
            ->select('count(g.id)')
            ->join('g.attributes', 'attributes')
            ->where(
                $qb->expr()->in('attributes', ':attribute')
            )
            ->setParameter('attribute', $attribute)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Get ordered groups by type
     *
     * @param GroupType $type
     *
     * @return array
     */
    protected function getGroupsByType(GroupType $type)
    {
        return $this
            ->getGroupsByTypeQB($type)
            ->getQuery()
            ->getResult();
    }

    /**
     * Get ordered groups query builder
     *
     * @param GroupType $type
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getGroupsByTypeQB(GroupType $type)
    {
        $alias = $this->getAlias();

        return $this->build()
            ->where($alias.'.type = :groupType')
            ->addOrderBy($alias.'.code', 'ASC')
            ->setParameter('groupType', $type);
    }

    /**
     * @return QueryBuilder
     */
    public function createDatagridQueryBuilder()
    {
        $qb = $this->createQueryBuilder('g');

        $groupLabelExpr = "(CASE WHEN translation.label IS NULL THEN g.code ELSE translation.label END)";
        $typeLabelExpr = "(CASE WHEN typTrans.label IS NULL THEN typ.code ELSE typTrans.label END)";
        $typeExpr = $qb->expr()->in('type.id', ':groupTypes');

        $qb
            ->addSelect(sprintf("%s AS groupLabel", $groupLabelExpr))
            ->addSelect(sprintf("%s AS typeLabel", $typeLabelExpr))
            ->addSelect('translation.label');

        $qb
            ->leftJoin('g.translations', 'translation', 'WITH', 'translation.locale = :localeCode')
            ->leftJoin('g.type', 'typ')
            ->leftJoin('typ.translations', 'typTrans', 'WITH', 'typTrans.locale = :localeCode')
            ->leftJoin('g.attributes', 'attribute')
            ->innerJoin('g.type', 'type', 'WITH', $typeExpr);

        return $qb;
    }

    /**
     * @return QueryBuilder
     */
    public function createAssociationDatagridQueryBuilder()
    {
        $qb = $this->createQueryBuilder('g');

        $groupLabelExpr = '(CASE WHEN translation.label IS NULL THEN g.code ELSE translation.label END)';
        $typeLabelExpr = '(CASE WHEN typTrans.label IS NULL THEN typ.code ELSE typTrans.label END)';

        $hasAssociationExpr =
            'CASE WHEN (pa IS NOT NULL OR g.id IN (:data_in)) AND g.id NOT IN (:data_not_in)' .
            'THEN true ELSE false END';

        $qb
            ->addSelect(sprintf('%s AS groupLabel', $groupLabelExpr))
            ->addSelect(sprintf('%s AS typeLabel', $typeLabelExpr))
            ->addSelect('translation.label')
            ->addSelect($hasAssociationExpr.' AS has_association');

        $qb
            ->leftJoin('g.translations', 'translation', 'WITH', 'translation.locale = :dataLocale')
            ->leftJoin('g.type', 'typ')
            ->leftJoin('typ.translations', 'typTrans', 'WITH', 'typTrans.locale = :dataLocale')
            ->leftJoin(
                'Pim\Bundle\CatalogBundle\Model\Association',
                'pa',
                'WITH',
                'pa.associationType = :associationType AND pa.owner = :product AND g MEMBER OF pa.groups'
            );

        return $qb;
    }

    /**
     * @return string
     */
    protected function getAlias()
    {
        return 'ProductGroup';
    }

    /**
     * @param integer $variantGroupId
     *
     * @return array product ids
     */
    public function getEligibleProductIds($variantGroupId)
    {
        $sql = 'SELECT count(ga.attribute_id) as nb '.
            'FROM pim_catalog_group_attribute as ga '.
            'WHERE ga.group_id = :groupId;';
        $stmt = $this->_em->getConnection()->prepare($sql);
        $stmt->bindValue('groupId', $variantGroupId);
        $stmt->execute();
        $nbAxes = $stmt->fetch()['nb'];

        $productMeta = $this->_em->getClassMetadata('Pim\Bundle\CatalogBundle\Model\Product');
        $valueClass = $productMeta->getAssociationMappings()['values']['targetEntity'];
        $valueTable = $this->_em->getClassMetadata($valueClass)->getTableName();

        $sql = 'SELECT v.entity_id '.
            'FROM pim_catalog_group_attribute as ga '.
            "LEFT JOIN {$valueTable} as v ON v.attribute_id = ga.attribute_id ".
            'WHERE ga.group_id = :groupId '.
            'GROUP BY v.entity_id '.
            'having count(v.option_id) = :nbAxes ;';
        $stmt = $this->_em->getConnection()->prepare($sql);
        $stmt->bindValue('groupId', $variantGroupId);
        $stmt->bindValue('nbAxes', $nbAxes);
        $stmt->execute();
        $results = $stmt->fetchAll();
        $productIds = array_map(
            function ($row) {
                return $row['entity_id'];
            },
            $results
        );

        return $productIds;
    }
}
