<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Doctrine\Repository;

use Doctrine\Common\Persistence\ObjectRepository;

/**
 * Proposition repository interface
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
interface PropositionRepositoryInterface extends ObjectRepository
{
    /**
     * Create the datagrid query builder
     *
     * @return QueryBuilder
     */
    public function createDatagridQueryBuilder();

    /**
     * Apply the context of the datagrid to the query
     *
     * @param QueryBuilder $qb
     * @param integer      $product
     *
     * @return PropositionRepositoryInterface
     */
    public function applyDatagridContext($qb, $productId);

    /**
     * Apply filter for datagrid
     *
     * @param QueryBuilder $qb
     * @param string       $field
     * @param string       $operator
     * @param mixed        $value
     */
    public function applyFilter($qb, $field, $operator, $value);

    /**
     * Apply filter for datagrid
     *
     * @param QueryBuilder $qb
     * @param string       $field
     * @param string       $direction
     */
    public function applySorter($qb, $field, $direction);
}
