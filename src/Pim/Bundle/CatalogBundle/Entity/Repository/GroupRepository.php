<?php

namespace Pim\Bundle\CatalogBundle\Entity\Repository;

use Pim\Bundle\CatalogBundle\Entity\GroupType;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\DataGridBundle\Model\DataGridRepositoryInterface;

/**
 * Group repository
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupRepository extends ReferableEntityRepository implements DatagridRepositoryInterface
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

        $choices = array();
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

        $choices = array();
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

        $qb
            ->addSelect(sprintf("%s AS groupLabel", $groupLabelExpr))
            ->addSelect(sprintf("%s AS typeLabel", $typeLabelExpr))
            ->addSelect('translation.label');

        $qb
            ->leftJoin('g.translations', 'translation', 'WITH', 'translation.locale = :localeCode')
            ->leftJoin('g.type', 'typ')
            ->leftJoin('typ.translations', 'typTrans', 'WITH', 'typTrans.locale = :localeCode');

        /*
        $joinExpr = $proxyQuery->expr()->neq('type.code', ':group');
        $proxyQuery
            ->innerJoin($proxyQuery->getRootAlias() .'.type', 'type', 'WITH', $joinExpr)
            ->setParameter('group', 'VARIANT');
         */

        return $qb;
    }

    /**
     * @return string
     */
    protected function getAlias()
    {
        return 'ProductGroup';
    }
}
