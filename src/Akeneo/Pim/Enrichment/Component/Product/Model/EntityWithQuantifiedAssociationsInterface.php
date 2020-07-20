<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Model;

use Akeneo\Pim\Enrichment\Component\Product\Model\QuantifiedAssociation\IdMapping;
use Akeneo\Pim\Enrichment\Component\Product\Model\QuantifiedAssociation\QuantifiedAssociationCollection;

/**
 * Interface to implement for any entity that should be aware of any quantified associations it is holding.
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface EntityWithQuantifiedAssociationsInterface
{
    /**
     * Get the quantified associations
     */
    public function getQuantifiedAssociations(): QuantifiedAssociationCollection;

    /**
     * Get all associated product ids
     *
     * @return int[]
     */
    public function getQuantifiedAssociationsProductIds(): array;

    /**
     * Get all associated product model ids
     *
     * @return int[]
     */
    public function getQuantifiedAssociationsProductModelIds(): array;

    /**
     * Remove quantified association with product/product model not present in parameter
     *
     * @param array $productIdentifiersToKeep
     * @param array $productModelCodesToKeep
     */
    public function filterQuantifiedAssociations(array $productIdentifiersToKeep, array $productModelCodesToKeep): void;

    /**
     * Remove all quantified associations
     */
    public function clearQuantifiedAssociations(): void;

    /**
     * Hydrates quantified associations from raw quantified associations
     *
     * @param IdMapping $mappedProductIds
     * @param IdMapping $mappedProductModelIds
     * @param array $associationTypeCodes
     */
    public function hydrateQuantifiedAssociations(
        IdMapping $mappedProductIds,
        IdMapping $mappedProductModelIds,
        array $associationTypeCodes
    ): void;

    /**
     * Get all associated product identifiers
     *
     * @return string[]
     */
    public function getQuantifiedAssociationsProductIdentifiers(): array;

    /**
     * Get all associated product model codes
     *
     * @return string[]
     */
    public function getQuantifiedAssociationsProductModelCodes(): array;

    /**
     * Update raw quantified associations from quantified associations
     *
     * @param IdMapping $mappedProductIdentifiers
     * @param IdMapping $mappedProductModelIdentifiers
     */
    public function updateRawQuantifiedAssociations(
        IdMapping $mappedProductIdentifiers,
        IdMapping $mappedProductModelIdentifiers
    ): void;

    /**
     * Normalize the quantified associations
     *
     * @return array
     */
    public function normalizeQuantifiedAssociations(): array;

    /**
     * Update quantified associations by merging with another quantified associations
     * @param QuantifiedAssociationCollection $quantifiedAssociations
     */
    public function mergeQuantifiedAssociations(QuantifiedAssociationCollection $quantifiedAssociations): void;

    /**
     * Update quantified associations by path
     * @param array $submittedQuantifiedAssociations
     */
    public function patchQuantifiedAssociations(array $submittedQuantifiedAssociations): void;
}
