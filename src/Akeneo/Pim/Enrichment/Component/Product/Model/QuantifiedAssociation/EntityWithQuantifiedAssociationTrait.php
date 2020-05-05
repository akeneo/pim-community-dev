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
    /** @var array */
    public $rawQuantifiedAssociations = [];

    /**
     * Not persisted.
     *
     * @var QuantifiedAssociations|null
     */
    public $quantifiedAssociations;

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
    public function hydrateQuantifiedAssociations(IdMapping $mappedProductIds, IdMapping $mappedProductModelIds): void
    {
        $this->quantifiedAssociations = QuantifiedAssociations::createWithAssociationsAndMapping($this->rawQuantifiedAssociations ?? [], $mappedProductIds, $mappedProductModelIds);
    }

    /**
     * @inheritDoc
     */
    public function getQuantifiedAssociationsProductIdentifiers(): array
    {
        if (null === $this->quantifiedAssociations) {
            return [];
        }

        return $this->quantifiedAssociations->getQuantifiedAssociationsProductIdentifiers();
    }

    /**
     * @inheritDoc
     */
    public function getQuantifiedAssociationsProductModelCodes(): array
    {
        if (null === $this->quantifiedAssociations) {
            return [];
        }

        return $this->quantifiedAssociations->getQuantifiedAssociationsProductModelCodes();
    }

    /**
     * @inheritDoc
     */
    public function updateRawQuantifiedAssociations(
        IdMapping $mappedProductIdentifiers,
        IdMapping $mappedProductModelIdentifiers
    ): void {
        if (null === $this->quantifiedAssociations) {
            $this->rawQuantifiedAssociations = [];

            return;
        }

        $this->rawQuantifiedAssociations = $this->quantifiedAssociations->normalizeWithMapping(
            $mappedProductIdentifiers,
            $mappedProductModelIdentifiers
        );
    }

    public function normalize(): array
    {
        return $this->quantifiedAssociations->normalize();
    }
}
