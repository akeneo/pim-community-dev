<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Model;

use Akeneo\Pim\Enrichment\Component\Product\Model\QuantifiedAssociation\IdMapping;
use Akeneo\Pim\Enrichment\Component\Product\Model\QuantifiedAssociation\QuantifiedAssociations;

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
     * Set the quantified associations
     */
    public function setQuantifiedAssociations(QuantifiedAssociations $quantifiedAssociations): void;

    /**
     * Get the quantified associations
     */
    public function getQuantifiedAssociations(): QuantifiedAssociations;

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
     * Hydrates quantified associations from raw quantified associations
     *
     * @param IdMapping $mappedProductIds
     * @param IdMapping $mappedProductModelIds
     */
    public function hydrateQuantifiedAssociations(IdMapping $mappedProductIds, IdMapping $mappedProductModelIds): void;

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
}
