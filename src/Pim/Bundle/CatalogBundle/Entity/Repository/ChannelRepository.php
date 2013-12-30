<?php

namespace Pim\Bundle\CatalogBundle\Entity\Repository;

/**
 * Channel repository
 * Define a default sort order by label
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ChannelRepository extends ReferableEntityRepository
{
    /**
     * {@inheritdoc}
     */
    public function findBy(array $criteria, array $orderBy = array('label' => 'ASC'), $limit = null, $offset = null)
    {
        return parent::findBy($criteria, $orderBy, $limit, $offset);
    }

    /**
     * {@inheritdoc}
     */
    public function findOneBy(array $criteria, array $orderBy = array('label' =>'ASC'))
    {
        return parent::findOneBy($criteria, $orderBy);
    }

    /**
     * Return the number of existing channels
     *
     * @return interger
     */
    public function countAll()
    {
        $qb = $this->createQueryBuilder('c');

        return $qb
            ->select('count(c.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @return QueryBuilder
     */
    public function createDatagridQueryBuilder()
    {
        $qb = $this->createQueryBuilder('c');
        $rootAlias = $qb->getRootAlias();

        $treeExpr = '(CASE WHEN ct.label IS NULL THEN category.code ELSE ct.label END)';

        $qb
            ->addSelect($rootAlias)
            ->addSelect('category')
            ->addSelect(sprintf('%s AS categoryLabel', $treeExpr))
            ->addSelect('ct.label');

        $qb
            ->innerJoin(sprintf('%s.category', $rootAlias), 'category')
            ->leftJoin('category.translations', 'ct', 'WITH', 'ct.locale = :localeCode');

        $qb->groupBy($rootAlias);

        return $qb;
    }
}
