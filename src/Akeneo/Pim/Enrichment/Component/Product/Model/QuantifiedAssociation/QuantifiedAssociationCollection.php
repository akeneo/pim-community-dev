<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Model\QuantifiedAssociation;

use Webmozart\Assert\Assert;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class QuantifiedAssociationCollection
{
    public const PRODUCT_MODELS_QUANTIFIED_LINKS_KEY = 'product_models';
    public const PRODUCTS_QUANTIFIED_LINKS_KEY = 'products';

    /** @var array */
    private $quantifiedAssociations;

    private function __construct(array $quantifiedAssociations)
    {
        $this->quantifiedAssociations = $quantifiedAssociations;
    }

    public static function createFromNormalized(array $normalizedQuantifiedAssociations): self
    {
        $mappedQuantifiedAssociations = [];

        foreach ($normalizedQuantifiedAssociations as $associationType => $associations) {
            if (!array_key_exists(self::PRODUCTS_QUANTIFIED_LINKS_KEY, $associations)) {
                $mappedQuantifiedAssociations[] = QuantifiedAssociation::createFromNormalized(
                    $associationType,
                    self::PRODUCTS_QUANTIFIED_LINKS_KEY,
                    []
                );
            }

            foreach ($associations as $quantifiedLinksType => $quantifiedLinksNormalized) {
                $mappedQuantifiedAssociations[] = QuantifiedAssociation::createFromNormalized(
                    $associationType,
                    $quantifiedLinksType,
                    $quantifiedLinksNormalized
                );
            }

            if (!array_key_exists(self::PRODUCT_MODELS_QUANTIFIED_LINKS_KEY, $associations)) {
                $mappedQuantifiedAssociations[] = QuantifiedAssociation::createFromNormalized(
                    $associationType,
                    self::PRODUCT_MODELS_QUANTIFIED_LINKS_KEY,
                    []
                );
            }
        }

        return new self($mappedQuantifiedAssociations);
    }

    public static function createWithAssociationsAndMapping(
        array $rawQuantifiedAssociations,
        IdMapping $mappedProductIds,
        IdMapping $mappedProductModelIds,
        array $associationTypeCodes
    ): self {
        $mappedQuantifiedAssociations = [];
        foreach ($rawQuantifiedAssociations as $associationType => $associations) {
            if (!in_array($associationType, $associationTypeCodes)) {
                continue;
            }

            Assert::keyExists($associations, self::PRODUCTS_QUANTIFIED_LINKS_KEY);
            Assert::keyExists($associations, self::PRODUCT_MODELS_QUANTIFIED_LINKS_KEY);

            $mappedQuantifiedAssociations[] = QuantifiedAssociation::createFromMapping(
                $associations[self::PRODUCTS_QUANTIFIED_LINKS_KEY],
                $associationType,
                self::PRODUCTS_QUANTIFIED_LINKS_KEY,
                $mappedProductIds
            );

            $mappedQuantifiedAssociations[] = QuantifiedAssociation::createFromMapping(
                $associations[self::PRODUCT_MODELS_QUANTIFIED_LINKS_KEY],
                $associationType,
                self::PRODUCT_MODELS_QUANTIFIED_LINKS_KEY,
                $mappedProductModelIds
            );
        }

        return new self($mappedQuantifiedAssociations);
    }

    public function patchQuantifiedAssociations(array $submittedQuantifiedAssociations): self
    {
        $currentQuantifiedAssociationNormalized = $this->normalize();

        $result = $this->normalize();
        foreach ($submittedQuantifiedAssociations as $submittedAssociationTypeCode => $submittedAssociations) {
            $result[$submittedAssociationTypeCode] = array_merge(
                $currentQuantifiedAssociationNormalized[$submittedAssociationTypeCode] ?? [],
                $submittedAssociations
            );
        }

        return self::createFromNormalized($result);
    }

    public function getQuantifiedAssociationsProductIdentifiers(): array
    {
        $result = array_map(function (QuantifiedAssociation $quantifiedAssociation) {
            return $quantifiedAssociation->getProductIdentifiers();
        }, $this->quantifiedAssociations);

        if (empty($result)) {
            return [];
        }

        return array_unique(array_merge(... $result));
    }

    public function getQuantifiedAssociationsProductModelCodes(): array
    {
        $result = array_map(function (QuantifiedAssociation $quantifiedAssociation) {
            return $quantifiedAssociation->getProductModelCodes();
        }, $this->quantifiedAssociations);

        if (empty($result)) {
            return [];
        }

        return array_unique(array_merge(... $result));
    }

    public function clearQuantifiedAssociations()
    {
        $quantifiedAssociationsCleared = array_fill_keys($this->getAssociationTypeCodes(), [
            self::PRODUCTS_QUANTIFIED_LINKS_KEY => [],
            self::PRODUCT_MODELS_QUANTIFIED_LINKS_KEY => [],
        ]);

        return self::createFromNormalized($quantifiedAssociationsCleared);
    }

    public function merge(QuantifiedAssociationCollection $quantifiedAssociations): self
    {
        $currentQuantifiedAssociationsNormalized = $this->normalizeWithIndexedIdentifiers();
        $quantifiedAssociationsToMergeNormalized = $quantifiedAssociations->normalizeWithIndexedIdentifiers();

        $mergedQuantifiedAssociationsNormalized = array_replace_recursive(
            $currentQuantifiedAssociationsNormalized,
            $quantifiedAssociationsToMergeNormalized
        );

        return self::createFromNormalized($mergedQuantifiedAssociationsNormalized);
    }

    public function normalizeWithMapping(IdMapping $mappedProductIdentifiers, IdMapping $mappedProductModelIdentifiers)
    {
        $quantifiedAssociationsNormalized = array_map(function (QuantifiedAssociation $association) use ($mappedProductIdentifiers, $mappedProductModelIdentifiers) {
            if ($association->isProductModelLinkType()) {
                return $association->normalizeWithMapping($mappedProductIdentifiers);
            }

            if ($association->isProductLinkType()) {
                return $association->normalizeWithMapping($mappedProductModelIdentifiers);
            }

            return [];
        }, $this->quantifiedAssociations);

        if (empty($quantifiedAssociationsNormalized)) {
            return [];
        }

        return array_merge_recursive(...$quantifiedAssociationsNormalized);
    }

    public function normalize(): array
    {
        $quantifiedAssociationsNormalized = array_map(function (QuantifiedAssociation $association) {
            return $association->normalize();
        }, $this->quantifiedAssociations);

        if (empty($quantifiedAssociationsNormalized)) {
            return [];
        }

        return array_merge_recursive(...$quantifiedAssociationsNormalized);
    }

    public function filterProductIdentifiers(array $productIdentifiersToKeep): QuantifiedAssociationCollection
    {
        $filteredQuantifiedAssociations = array_map(function (QuantifiedAssociation $quantifiedAssociation) use ($productIdentifiersToKeep) {
            return $quantifiedAssociation->filterProductIdentifiers($productIdentifiersToKeep);
        }, $this->quantifiedAssociations);

        return new self($filteredQuantifiedAssociations);
    }

    public function filterProductModelCodes(array $productModelCodesToKeep): QuantifiedAssociationCollection
    {
        $filteredQuantifiedAssociations = array_map(function (QuantifiedAssociation $quantifiedAssociation) use ($productModelCodesToKeep) {
            return $quantifiedAssociation->filterProductModelCodes($productModelCodesToKeep);
        }, $this->quantifiedAssociations);

        return new self($filteredQuantifiedAssociations);
    }

    private function getAssociationTypeCodes()
    {
        $associationTypeCodes = array_map(function (QuantifiedAssociation $association) {
            return $association->getAssociationTypeCode();
        }, $this->quantifiedAssociations);

        return array_values(array_unique($associationTypeCodes));
    }

    private function normalizeWithIndexedIdentifiers()
    {
        $quantifiedAssociationsNormalized = $this->normalize();

        $result = [];
        foreach ($quantifiedAssociationsNormalized as $associationType => $associationsNormalized) {
            foreach ($associationsNormalized as $quantifiedLinksType => $quantifiedLinksNormalized) {
                $result[$associationType][$quantifiedLinksType] = [];
                foreach ($quantifiedLinksNormalized as $quantifiedLinkNormalized) {
                    $identifier = $quantifiedLinkNormalized['identifier'];

                    $result[$associationType][$quantifiedLinksType][$identifier] = $quantifiedLinkNormalized;
                }
            }
        }

        return $result;
    }
}
