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
    public ?array $rawQuantifiedAssociations = null;

    /**
     * Not persisted.
     */
    protected ?QuantifiedAssociationCollection $quantifiedAssociationCollection = null;

    /**
     * @inheritDoc
     */
    public function filterQuantifiedAssociations(array $productIdentifiersToKeep, array $productModelCodesToKeep): void
    {
        if (null === $this->quantifiedAssociationCollection) {
            return;
        }

        $this->quantifiedAssociationCollection = $this->quantifiedAssociationCollection
            ->filterProductIdentifiers($productIdentifiersToKeep)
            ->filterProductModelCodes($productModelCodesToKeep);
    }

    /**
     * @inheritDoc
     */
    public function getQuantifiedAssociations(): QuantifiedAssociationCollection
    {
        return $this->quantifiedAssociationCollection;
    }

    /**
     * @inheritDoc
     */
    public function mergeQuantifiedAssociations(QuantifiedAssociationCollection $quantifiedAssociations): void
    {
        if ($this->quantifiedAssociationCollection === null) {
            return;
        }

        $this->quantifiedAssociationCollection = $this->quantifiedAssociationCollection->merge($quantifiedAssociations);
    }

    /**
     * @inheritDoc
     */
    public function patchQuantifiedAssociations(array $submittedQuantifiedAssociations): void
    {
        if (null === $this->quantifiedAssociationCollection) {
            return;
        }

        $this->quantifiedAssociationCollection = $this->quantifiedAssociationCollection->patchQuantifiedAssociations(
            $submittedQuantifiedAssociations
        );
    }

    /**
     * @inheritDoc
     */
    public function clearQuantifiedAssociations(): void
    {
        if (null === $this->quantifiedAssociationCollection) {
            return;
        }

        $this->quantifiedAssociationCollection = $this->quantifiedAssociationCollection->clearQuantifiedAssociations();
    }

    /**
     * @inheritDoc
     */
    public function getQuantifiedAssociationsProductIds(): array
    {
        if (null === $this->rawQuantifiedAssociations) {
            return [];
        }

        $result = [];
        foreach ($this->rawQuantifiedAssociations as $associationType => $associations) {
            foreach ($associations['products'] as $productAssociation) {
                $result[] = $productAssociation['id'];
            }
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function getQuantifiedAssociationsProductModelIds(): array
    {
        if (null === $this->rawQuantifiedAssociations) {
            return [];
        }

        $result = [];
        foreach ($this->rawQuantifiedAssociations as $associationType => $associations) {
            foreach ($associations['product_models'] as $productModelAssociation) {
                $result[] = $productModelAssociation['id'];
            }
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function hydrateQuantifiedAssociations(
        IdMapping $mappedProductIds,
        IdMapping $mappedProductModelIds,
        array $associationTypeCodes
    ): void {
        $this->quantifiedAssociationCollection = QuantifiedAssociationCollection::createWithAssociationsAndMapping(
            $this->rawQuantifiedAssociations ?? [],
            $mappedProductIds,
            $mappedProductModelIds,
            $associationTypeCodes
        );
    }

    /**
     * @inheritDoc
     */
    public function getQuantifiedAssociationsProductIdentifiers(): array
    {
        if (null === $this->quantifiedAssociationCollection) {
            return [];
        }

        return $this->quantifiedAssociationCollection->getQuantifiedAssociationsProductIdentifiers();
    }

    /**
     * @inheritDoc
     */
    public function getQuantifiedAssociationsProductModelCodes(): array
    {
        if (null === $this->quantifiedAssociationCollection) {
            return [];
        }

        return $this->quantifiedAssociationCollection->getQuantifiedAssociationsProductModelCodes();
    }

    /**
     * @inheritDoc
     */
    public function updateRawQuantifiedAssociations(
        IdMapping $mappedProductIdentifiers,
        IdMapping $mappedProductModelIdentifiers
    ): void {
        if (null === $this->quantifiedAssociationCollection) {
            return;
        }

        $normalized = $this->quantifiedAssociationCollection->normalizeWithMapping(
            $mappedProductIdentifiers,
            $mappedProductModelIdentifiers
        );

        // In the database, `rawQuantifiedAssociations` is `null` by default.
        // Replacing `null` by `[]` will trigger doctrine events and the versionning.
        // Instead, we store `null` if there is no quantified associations.
        $this->rawQuantifiedAssociations = empty($normalized) ? null : $normalized;
    }

    /**
     * @inheritDoc
     */
    public function normalizeQuantifiedAssociations(): array
    {
        if (null === $this->quantifiedAssociationCollection) {
            return [];
        }

        return $this->quantifiedAssociationCollection->normalize();
    }
}
