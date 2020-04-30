<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Model\QuantifiedAssociation;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
trait EntityWithQuantifiedAssociationTrait
{
    /**
     * @var array
     */
    protected $rawQuantifiedAssociations = [];

    /**
     * Not persisted. Loaded on the fly via the $rawValues.
     *
     * @var array
     */
    protected $quantifiedAssociations = [];

    /**
     * @inheritDoc
     */
    public function getQuantifiedAssociationsProductIds(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getQuantifiedAssociationsProductModelIds(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function hydrateQuantifiedAssociations(IdMapping $mappedProductIds, IdMapping $mappedProductModelIds): void
    {
        $this->quantifiedAssociations = QuantifiedAssociations::createWithAssociationsAndMapping($this->rawQuantifiedAssociations, $mappedProductIds, $mappedProductModelIds);
    }

    /**
     * @inheritDoc
     */
    public function getQuantifiedAssociationsProductIdentifiers(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getQuantifiedAssociationsProductModelCodes(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function updateRawQuantifiedAssociations(
        IdMapping $mappedProductIdentifiers,
        IdMapping $mappedProductModelIdentifiers
    ): void {
        $this->rawQuantifiedAssociations = $this->quantifiedAssociations;
    }
}
