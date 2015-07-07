<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM\Sorter;

use Doctrine\ORM\QueryBuilder;
use Pim\Bundle\CatalogBundle\Query\Sorter\FieldSorterInterface;

/**
 * In group sorter
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InGroupSorter implements FieldSorterInterface
{
    /** @var QueryBuilder */
    protected $qb;

    /**
     * {@inheritdoc}
     */
    public function setQueryBuilder($queryBuilder)
    {
        $this->qb = $queryBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsField($field)
    {
        return (strpos($field, 'in_group_') !== false);
    }

    /**
     * {@inheritdoc}
     */
    public function addFieldSorter($field, $direction, $locale = null, $scope = null)
    {
        $alias = 'inGroupSorter';
        $inGroupExpr = 'CASE WHEN :currentGroup MEMBER OF p.groups THEN true ELSE false END';
        $this->qb
            ->addSelect(sprintf('%s AS %s', $inGroupExpr, $alias))
            ->addOrderBy($alias, $direction);

        $idField = $this->qb->getRootAlias().'.id';
        $this->qb->addOrderBy($idField);

        return $this;
    }
}
