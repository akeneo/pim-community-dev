<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Component\Repository;

use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\QueryBuilder;
use Pim\Component\Catalog\Model\EntityWithValuesInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
interface EntityWithValuesDraftRepositoryInterface extends ObjectRepository
{
    /**
     * Return entities with values based on user
     */
    public function findUserEntityWithValuesDraft(EntityWithValuesInterface $entityWithValues, string $username): ?EntityWithValuesInterface;

    /**
     * Create the datagrid query builder
     */
    public function createDatagridQueryBuilder(): QueryBuilder;

    /**
     * Return entity with values drafts that can be approved by the given user
     */
    public function findApprovableByUser(UserInterface $user, ?int $limit = null): ?array;

    /**
     * Apply the context of the datagrid to the query
     */
    public function applyDatagridContext(QueryBuilder $qb, ?string $entityWithValuesId): EntityWithValuesDraftRepositoryInterface;

    /**
     * Apply filter for datagrid
     */
    public function applyFilter(QueryBuilder $qb, string $field, string $operator, $value): void;

    /**
     * Apply filter for datagrid
     */
    public function applySorter(QueryBuilder $qb, string $field, ?string $direction): void;

    /**
     * Find all by product
     */
    public function findByEntityWithValues(EntityWithValuesInterface $entityWithValues): ?array;

    /**
     * Find all drafts corresponding to the specified ids
     */
    public function findByIds(array $ids): ?array;

    /**
     * Returns the total count of entity with values drafts
     */
    public function countAll(): int;
}
