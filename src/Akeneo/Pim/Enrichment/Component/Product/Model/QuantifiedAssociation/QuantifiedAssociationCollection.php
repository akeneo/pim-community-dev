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
    private const PRODUCT_MODELS_QUANTIFIED_LINKS_KEY = 'product_models';
    private const PRODUCTS_QUANTIFIED_LINKS_KEY = 'products';

    /** @var array */
    private $quantifiedAssociations;

    private function __construct(
        array $quantifiedAssociations
    ) {
        $this->quantifiedAssociations = $quantifiedAssociations;
    }

    public static function createFromNormalized(array $normalizedQuantifiedAssociations): self
    {
        $mappedQuantifiedAssociations = [];

        foreach ($normalizedQuantifiedAssociations as $associationType => $associations) {
            $mappedQuantifiedAssociations[$associationType] = [
                self::PRODUCTS_QUANTIFIED_LINKS_KEY => [],
                self::PRODUCT_MODELS_QUANTIFIED_LINKS_KEY => [],
            ];

            foreach ($associations as $quantifiedLinksType => $quantifiedLinks) {
                if (!isset($mappedQuantifiedAssociations[$associationType][$quantifiedLinksType])) {
                    $mappedQuantifiedAssociations[$associationType][$quantifiedLinksType] = [];
                }

                foreach ($quantifiedLinks as $association) {
                    Assert::isArray($association);
                    Assert::keyExists($association, 'identifier');
                    Assert::keyExists($association, 'quantity');

                    $quantifiedLink = new QuantifiedLink(
                        $association['identifier'],
                        $association['quantity']
                    );

                    $mappedQuantifiedAssociations[$associationType][$quantifiedLinksType][] = $quantifiedLink;
                }
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

            $mappedQuantifiedAssociations[$associationType] = [
                self::PRODUCTS_QUANTIFIED_LINKS_KEY => [],
                self::PRODUCT_MODELS_QUANTIFIED_LINKS_KEY => [],
            ];

            foreach ($associations[self::PRODUCTS_QUANTIFIED_LINKS_KEY] ?? [] as $productAssociation) {
                Assert::keyExists($productAssociation, 'id');
                Assert::keyExists($productAssociation, 'quantity');

                if ($mappedProductIds->hasIdentifier($productAssociation['id'])) {
                    $quantifiedLink = new QuantifiedLink(
                        $mappedProductIds->getIdentifier($productAssociation['id']),
                        $productAssociation['quantity']
                    );
                    $mappedQuantifiedAssociations[$associationType][self::PRODUCTS_QUANTIFIED_LINKS_KEY][] = $quantifiedLink;
                }
            }

            foreach ($associations[self::PRODUCT_MODELS_QUANTIFIED_LINKS_KEY] ?? [] as $productModelAssociation) {
                Assert::keyExists($productModelAssociation, 'id');
                Assert::keyExists($productModelAssociation, 'quantity');

                if ($mappedProductModelIds->hasIdentifier($productModelAssociation['id'])) {
                    $quantifiedLink = new QuantifiedLink(
                        $mappedProductModelIds->getIdentifier($productModelAssociation['id']),
                        $productModelAssociation['quantity']
                    );
                    $mappedQuantifiedAssociations[$associationType][self::PRODUCT_MODELS_QUANTIFIED_LINKS_KEY][] = $quantifiedLink;
                }
            }
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
        $result = [];
        foreach ($this->quantifiedAssociations as $associationType => $associations) {
            /** @var QuantifiedLink $quantifiedLink */
            foreach ($associations[self::PRODUCTS_QUANTIFIED_LINKS_KEY] as $quantifiedLink) {
                $result[] = $quantifiedLink->identifier();
            }
        }

        return array_unique($result);
    }

    public function getQuantifiedAssociationsProductModelCodes(): array
    {
        $result = [];
        foreach ($this->quantifiedAssociations as $associationType => $associations) {
            /** @var QuantifiedLink $quantifiedLink */
            foreach ($associations[self::PRODUCT_MODELS_QUANTIFIED_LINKS_KEY] as $quantifiedLink) {
                $result[] = $quantifiedLink->identifier();
            }
        }

        return array_unique($result);
    }

    public function clearQuantifiedAssociations()
    {
        $quantifiedAssociationsCleared = array_fill_keys(
            $this->getAssociationTypeCodes(),
            ['products' => [], 'product_models' => []]
        );

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
        $result = [];
        foreach ($this->quantifiedAssociations as $associationType => $associations) {
            $result[$associationType] = [
                self::PRODUCTS_QUANTIFIED_LINKS_KEY => [],
                self::PRODUCT_MODELS_QUANTIFIED_LINKS_KEY => [],
            ];

            /** @var QuantifiedLink $quantifiedLink */
            foreach ($associations[self::PRODUCTS_QUANTIFIED_LINKS_KEY] as $quantifiedLink) {
                $normalizedQuantifiedLink = $quantifiedLink->normalize();
                $result[$associationType][self::PRODUCTS_QUANTIFIED_LINKS_KEY][] = [
                    'id' => $mappedProductIdentifiers->getId($normalizedQuantifiedLink['identifier']),
                    'quantity' => $normalizedQuantifiedLink['quantity']
                ];
            }

            /** @var QuantifiedLink $quantifiedLink */
            foreach ($associations[self::PRODUCT_MODELS_QUANTIFIED_LINKS_KEY] as $quantifiedLink) {
                $normalizedQuantifiedLink = $quantifiedLink->normalize();
                $result[$associationType][self::PRODUCT_MODELS_QUANTIFIED_LINKS_KEY][] = [
                    'id' => $mappedProductModelIdentifiers->getId($normalizedQuantifiedLink['identifier']),
                    'quantity' => $normalizedQuantifiedLink['quantity']
                ];
            }
        }

        return $result;
    }

    public function normalize(): array
    {
        $result = [];
        foreach ($this->quantifiedAssociations as $associationType => $associations) {
            $result[$associationType] = [
                self::PRODUCTS_QUANTIFIED_LINKS_KEY => [],
                self::PRODUCT_MODELS_QUANTIFIED_LINKS_KEY => [],
            ];

            foreach ($associations as $quantifiedLinksType => $quantifiedLinks) {
                if (!isset($result[$associationType][$quantifiedLinksType])) {
                    $result[$associationType][$quantifiedLinksType] = [];
                }
                /** @var QuantifiedLink $quantifiedLink */
                foreach ($quantifiedLinks as $quantifiedLink) {
                    $result[$associationType][$quantifiedLinksType][] = $quantifiedLink->normalize();
                }
            }
        }

        return $result;
    }

    public function filterProductIdentifiers(array $productIdentifiersToKeep): QuantifiedAssociationCollection
    {
        $filteredQuantifiedAssociations = [];
        foreach ($this->quantifiedAssociations as $associationTypeCode => $quantifiedAssociation) {
            $filteredQuantifiedAssociations[$associationTypeCode]['product_models'] = $quantifiedAssociation['product_models'];
            $filteredQuantifiedAssociations[$associationTypeCode]['products'] = array_filter(
                $quantifiedAssociation['products'],
                function (QuantifiedLink $quantifiedLink) use ($productIdentifiersToKeep) {
                    return in_array($quantifiedLink->identifier(), $productIdentifiersToKeep);
                }
            );
        }

        return new self($filteredQuantifiedAssociations);
    }

    public function filterProductModelCodes(array $productModelCodesToKeep): QuantifiedAssociationCollection
    {
        $filteredQuantifiedAssociations = [];
        foreach ($this->quantifiedAssociations as $associationTypeCode => $quantifiedAssociation) {
            $filteredQuantifiedAssociations[$associationTypeCode]['products'] = $quantifiedAssociation['products'];
            $filteredQuantifiedAssociations[$associationTypeCode]['product_models'] = array_filter(
                $quantifiedAssociation['product_models'],
                function (QuantifiedLink $quantifiedLink) use ($productModelCodesToKeep) {
                    return in_array($quantifiedLink->identifier(), $productModelCodesToKeep);
                }
            );
        }

        return new self($filteredQuantifiedAssociations);
    }

    public function equals(QuantifiedAssociationCollection $otherCollection): bool
    {
        $sortByIdentifiers = fn (
            array $quantifiedLinkA,
            array $quantifiedLinkB
        ): int => $quantifiedLinkA['identifier'] <=> $quantifiedLinkB['identifier'];

        $normalized = $this->normalize();
        ksort($normalized);
        foreach ($normalized as $associationType => $associations) {
            usort($normalized[$associationType][self::PRODUCTS_QUANTIFIED_LINKS_KEY], $sortByIdentifiers);
            usort($normalized[$associationType][self::PRODUCT_MODELS_QUANTIFIED_LINKS_KEY], $sortByIdentifiers);
        }

        $otherNormalized = $otherCollection->normalize();
        ksort($otherNormalized);
        foreach ($otherNormalized as $associationType => $associations) {
            usort($otherNormalized[$associationType][self::PRODUCTS_QUANTIFIED_LINKS_KEY], $sortByIdentifiers);
            usort($otherNormalized[$associationType][self::PRODUCT_MODELS_QUANTIFIED_LINKS_KEY], $sortByIdentifiers);
        }

        return $normalized === $otherNormalized;
    }

    private function getAssociationTypeCodes()
    {
        return array_keys($this->quantifiedAssociations);
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
