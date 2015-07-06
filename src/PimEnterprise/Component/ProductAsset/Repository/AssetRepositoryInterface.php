<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ProductAsset\Repository;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\QueryBuilder;
use Pim\Component\ReferenceData\Repository\ReferenceDataRepositoryInterface;

/**
 * Product asset repository interface
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 */
interface AssetRepositoryInterface extends
    ObjectRepository,
    IdentifiableObjectRepositoryInterface,
    ReferenceDataRepositoryInterface
{
    /**
     * Create the datagrid query builder for the asset grid
     *
     * @param array $parameters
     *
     * @return QueryBuilder
     */
    public function createAssetDatagridQueryBuilder(array $parameters = []);

    /**
     * Apply tag filter
     *
     * @param QueryBuilder $qb
     * @param string       $field
     * @param string       $operator
     * @param mixed        $value
     */
    public function applyTagFilter(QueryBuilder $qb, $field, $operator, $value);

    /**
     * Find assets by identifiers
     *
     * @param array $identifiers
     *
     * @return ArrayCollection
     */
    public function findByIdentifiers(array $identifiers = []);
}
