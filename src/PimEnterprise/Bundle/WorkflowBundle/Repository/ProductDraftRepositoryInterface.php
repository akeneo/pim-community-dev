<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\Repository;

use Doctrine\Common\Persistence\ObjectRepository;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use PimEnterprise\Bundle\WorkflowBundle\Model\ProductDraftInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * ProductDraftInterface repository interface
 *
 * @author Romain Monceau <romain@akeneo.com>
 */
interface ProductDraftRepositoryInterface extends ObjectRepository
{
    /**
     * Create the datagrid query builder
     *
     * @return \Doctrine\ORM\QueryBuilder|\Doctrine\ODM\MongoDB\Query\Builder
     */
    public function createDatagridQueryBuilder();

    /**
     * Create the datagrid query builder for the proposal grid
     *
     * @param array $parameters
     *
     * @return \Doctrine\ORM\QueryBuilder|\Doctrine\ODM\MongoDB\Query\Builder
     */
    public function createProposalDatagridQueryBuilder(array $parameters = []);

    /**
     * Return product drafts that can be approved by the given user
     *
     * @param UserInterface $user
     * @param int           $limit
     *
     * @return ProductDraftInterface[]|null
     */
    public function findApprovableByUser(UserInterface $user, $limit = null);

    /**
     * Apply the context of the datagrid to the query
     *
     * @param \Doctrine\ORM\QueryBuilder|\Doctrine\ODM\MongoDB\Query\Builder $qb
     * @param int|string                                                     $productId
     *
     * @return ProductDraftRepositoryInterface
     */
    public function applyDatagridContext($qb, $productId);

    /**
     * Apply filter for datagrid
     *
     * @param \Doctrine\ORM\QueryBuilder|\Doctrine\ODM\MongoDB\Query\Builder $qb
     * @param string                                                         $field
     * @param string                                                         $operator
     * @param mixed                                                          $value
     */
    public function applyFilter($qb, $field, $operator, $value);

    /**
     * Apply filter for datagrid
     *
     * @param \Doctrine\ORM\QueryBuilder|\Doctrine\ODM\MongoDB\Query\Builder $qb
     * @param string                                                         $field
     * @param string                                                         $direction
     */
    public function applySorter($qb, $field, $direction);

    /**
     * Find one user product draft by its locale
     *
     * @param ProductInterface $product
     * @param string           $username
     *
     * @return ProductDraftInterface|null
     */
    public function findUserProductDraft(ProductInterface $product, $username);

    /**
     * Find all by product
     *
     * @param ProductInterface $product
     *
     * @return ProductDraftInterface[]|null
     */
    public function findByProduct(ProductInterface $product);

    /**
     * Find all drafts corresponding to the specified ids
     *
     * @param array $ids
     *
     * @return ProductDraftInterface[]|null
     */
    public function findByIds(array $ids);

    /**
     * @return string[]
     */
    public function getDistinctAuthors();

    /**
     * @param mixed $qb
     * @param bool  $inset
     * @param array $values
     *
     * @return mixed
     */
    public function applyMassActionParameters($qb, $inset, array $values);
}
