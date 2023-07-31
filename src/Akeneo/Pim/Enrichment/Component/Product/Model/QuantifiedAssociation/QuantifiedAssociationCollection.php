<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Model\QuantifiedAssociation;

use Ramsey\Uuid\UuidInterface;
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

    private function __construct(
        private array $quantifiedAssociations
    ) {
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
                    if (!array_key_exists('identifier', $association) && !array_key_exists('uuid', $association)) {
                        throw new \InvalidArgumentException('Expected one of the keys "identifier" or "uuid" to exist.');
                    }
                    Assert::keyExists($association, 'quantity');

                    if (isset($association['uuid'])) {
                        $quantifiedLink = QuantifiedLink::fromUuid(
                            $association['uuid'],
                            $association['identifier'] ?? null,
                            $association['quantity']
                        );
                    } else {
                        $quantifiedLink = QuantifiedLink::fromIdentifier(
                            $association['identifier'],
                            $association['quantity']
                        );
                    }

                    $mappedQuantifiedAssociations[$associationType][$quantifiedLinksType][] = $quantifiedLink;
                }
            }
        }

        return new self($mappedQuantifiedAssociations);
    }

    public static function createWithAssociationsAndMapping(
        array $rawQuantifiedAssociations,
        UuidMapping $mappedProductIds,
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
                Assert::isArray($productAssociation);
                Assert::keyExists($productAssociation, 'id');
                Assert::keyExists($productAssociation, 'quantity');

                if (isset($productAssociation['id']) && (
                    $mappedProductIds->hasIdentifierFromId($productAssociation['id'])
                    || $mappedProductIds->hasUuidFromId($productAssociation['id'])
                )) {
                    $quantifiedLink = QuantifiedLink::fromUuid(
                        $mappedProductIds->getUuidFromId($productAssociation['id']),
                        $mappedProductIds->getIdentifierFromId($productAssociation['id']),
                        $productAssociation['quantity']
                    );
                    $mappedQuantifiedAssociations[$associationType][self::PRODUCTS_QUANTIFIED_LINKS_KEY][] = $quantifiedLink;
                }
            }

            foreach ($associations[self::PRODUCT_MODELS_QUANTIFIED_LINKS_KEY] ?? [] as $productModelAssociation) {
                Assert::keyExists($productModelAssociation, 'id');
                Assert::keyExists($productModelAssociation, 'quantity');

                if ($mappedProductModelIds->hasIdentifier($productModelAssociation['id'])) {
                    $quantifiedLink = QuantifiedLink::fromIdentifier(
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
        $result = $this->normalize(); // wrong format is coming from $submittedQuantifiedAssociations and merge below adds it to result
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
        foreach ($this->quantifiedAssociations as $associations) {
            /** @var QuantifiedLink $quantifiedLink */
            foreach ($associations[self::PRODUCTS_QUANTIFIED_LINKS_KEY] as $quantifiedLink) {
                if (null !== $quantifiedLink->identifier()) {
                    $result[] = $quantifiedLink->identifier();
                }
            }
        }

        return array_unique($result);
    }

    public function getQuantifiedAssociationsProductUuids(): array
    {
        $result = [];
        foreach ($this->quantifiedAssociations as $associations) {
            /** @var QuantifiedLink $quantifiedLink */
            foreach ($associations[self::PRODUCTS_QUANTIFIED_LINKS_KEY] as $quantifiedLink) {
                if (null !== $quantifiedLink->uuid()) {
                    $result[] = $quantifiedLink->uuid();
                }
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
        $currentQuantifiedAssociationsNormalized = $this->normalize();
        $quantifiedAssociationsToMergeNormalized = $quantifiedAssociations->normalize();

        foreach ($quantifiedAssociationsToMergeNormalized as $associationType => $quantifiedAssociationToMergeNormalized) {
            if (!isset($currentQuantifiedAssociationsNormalized[$associationType])) {
                $currentQuantifiedAssociationsNormalized[$associationType] = $quantifiedAssociationToMergeNormalized;
                continue;
            }

            $currentQuantifiedLinks = $currentQuantifiedAssociationsNormalized[$associationType];
            foreach ($quantifiedAssociationToMergeNormalized as $quantifiedLinkType => $quantifiedLinksToMerge) {
                if (!isset($currentQuantifiedLinks[$quantifiedLinkType])) {
                    $currentQuantifiedAssociationsNormalized[$associationType][$quantifiedLinkType] = $quantifiedLinksToMerge;
                    continue;
                }

                $currentQuantifiedAssociationsNormalized[$associationType][$quantifiedLinkType] = $this->mergeQuantifiedLinks(
                    $currentQuantifiedLinks[$quantifiedLinkType],
                    $quantifiedLinksToMerge,
                );
            }
        }

        return self::createFromNormalized($currentQuantifiedAssociationsNormalized);
    }

    private function mergeQuantifiedLinks(array $currentQuantifiedLinks, array $quantifiedLinksToMerge): array
    {
        $mergedQuantifiedLinks = array_reverse(array_merge($currentQuantifiedLinks, $quantifiedLinksToMerge));

        $uuidList = [];
        $identifierList = [];

        foreach ($mergedQuantifiedLinks as $index => $mergedQuantifiedLink) {
            if (
                isset($mergedQuantifiedLink['uuid']) && in_array($mergedQuantifiedLink['uuid'], $uuidList)
                || isset($mergedQuantifiedLink['identifier']) && in_array($mergedQuantifiedLink['identifier'], $identifierList)
            ) {
                unset($mergedQuantifiedLinks[$index]);
                continue;
            }

            if (isset($mergedQuantifiedLink['uuid'])) {
                $uuidList[] = $mergedQuantifiedLink['uuid'];
            }

            if (isset($mergedQuantifiedLink['identifier'])) {
                $identifierList[] = $mergedQuantifiedLink['identifier'];
            }
        }

        return array_reverse(array_values($mergedQuantifiedLinks));
    }

    /**
     * @param UuidMapping $uuidMappedProductIdentifiers
     * @param IdMapping $mappedProductModelIdentifiers
     * @return array
     */
    public function normalizeWithMapping(
        UuidMapping $uuidMappedProductIdentifiers,
        IdMapping $mappedProductModelIdentifiers
    ) {
        $result = [];

        foreach ($this->quantifiedAssociations as $associationType => $associations) {
            $result[$associationType] = [
                self::PRODUCTS_QUANTIFIED_LINKS_KEY => [],
                self::PRODUCT_MODELS_QUANTIFIED_LINKS_KEY => [],
            ];

            /** @var QuantifiedLink $quantifiedLink */
            foreach ($associations[self::PRODUCTS_QUANTIFIED_LINKS_KEY] as $quantifiedLink) {
                if (null !== $quantifiedLink->uuid()) {
                    // The link is done by uuid
                    $normalizedQuantifiedLink = $quantifiedLink->normalize();
                    $id = $uuidMappedProductIdentifiers->getIdFromUuid($normalizedQuantifiedLink['uuid']);
                    if (null !== $id) {
                        $result[$associationType][self::PRODUCTS_QUANTIFIED_LINKS_KEY][] = [
                            'id' => $id,
                            'uuid' => $normalizedQuantifiedLink['uuid'],
                            'quantity' => $normalizedQuantifiedLink['quantity'],
                        ];
                    }
                } else {
                    // The link is done by identifier
                    $normalizedQuantifiedLink = $quantifiedLink->normalize();
                    if ($uuidMappedProductIdentifiers->hasUuid($normalizedQuantifiedLink['identifier'])) {
                        $uuid = $uuidMappedProductIdentifiers->getUuidFromIdentifier($normalizedQuantifiedLink['identifier']);
                        $result[$associationType][self::PRODUCTS_QUANTIFIED_LINKS_KEY][] = [
                            'id' => $uuidMappedProductIdentifiers->getIdFromIdentifier($normalizedQuantifiedLink['identifier']),
                            'uuid' => $uuid->toString(),
                            'quantity' => $normalizedQuantifiedLink['quantity']
                        ];
                    }
                }
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
                    return null === $quantifiedLink->identifier() || in_array($quantifiedLink->identifier(), $productIdentifiersToKeep);
                }
            );
        }

        return new self($filteredQuantifiedAssociations);
    }

    /**
     * @param UuidInterface[] $productUuidsToKeep
     *
     * @return QuantifiedAssociationCollection
     */
    public function filterProductUuids(array $productUuidsToKeep)
    {
        $filteredQuantifiedAssociations = [];
        $productUuidsToKeepAsStr = array_map(
            fn (UuidInterface $uuid): string => $uuid->toString(),
            $productUuidsToKeep
        );
        foreach ($this->quantifiedAssociations as $associationTypeCode => $quantifiedAssociation) {
            $filteredQuantifiedAssociations[$associationTypeCode]['product_models'] = $quantifiedAssociation['product_models'];
            $filteredQuantifiedAssociations[$associationTypeCode]['products'] = array_filter(
                $quantifiedAssociation['products'],
                function (QuantifiedLink $quantifiedLink) use ($productUuidsToKeepAsStr) {
                    return null === $quantifiedLink->uuid() ||
                        in_array($quantifiedLink->uuid()->toString(), $productUuidsToKeepAsStr);
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
        ): int => ($quantifiedLinkA['identifier'] ?? $quantifiedLinkA['uuid'] ?? '')
            <=> ($quantifiedLinkB['identifier'] ?? $quantifiedLinkB['uuid'] ?? '');

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
}
