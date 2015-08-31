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
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\QueryBuilder;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Component\ReferenceData\Repository\ReferenceDataRepositoryInterface;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;

/**
 * Product asset repository interface
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
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

    /**
     * Find all codes that begin by "$code"
     *
     * @param string $code
     *
     * @return string[] Array with codes inside
     */
    public function findSimilarCodes($code);

    /**
     * Count complete assets among the given asset ids
     *
     * @param int[] $assetIds
     * @param int   $localeId
     * @param int   $channelId
     *
     * @throws \Doctrine\DBAL\DBALException
     *
     * @return int
     */
    public function countCompleteAssets(array $assetIds, $localeId, $channelId);

    /**
     * Retrieve products linked to an asset
     *
     * @param AssetInterface $asset
     *
     * @return ProductInterface[]
     */
    public function findProducts(AssetInterface $asset);

    /**
     * @param \DateTime $now
     * @param int       $delay
     *
     * @return AssetInterface[]
     */
    public function findExpiringAssets(\DateTime $now, $delay = 5);

    /**
     * Retrieve an asset by its code
     *
     * @param string $assetCode
     *
     * @return AssetInterface|null
     */
    public function findOneByCode($assetCode);
}
