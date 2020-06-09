<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Model\QuantifiedAssociation;

use Webmozart\Assert\Assert;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class QuantifiedAssociations
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
        IdMapping $mappedProductModelIds
    ): self {
        $mappedQuantifiedAssociations = [];
        foreach ($rawQuantifiedAssociations as $associationType => $associations) {
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

    public function getQuantifiedAssociationsProductIdentifiers(): array
    {
        $result = [];
        foreach ($this->quantifiedAssociations as $associationType => $associations) {
            /** @var QuantifiedLink $quantifiedLink */
            foreach ($associations[self::PRODUCTS_QUANTIFIED_LINKS_KEY] as $quantifiedLink) {
                $result[] = $quantifiedLink->identifier();
            }
        }

        return $result;
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

        return $result;
    }

    public function merge(QuantifiedAssociations $quantifiedAssociations): void
    {
        $normalizedQuantifiedAssociationsToMerge = $quantifiedAssociations->normalize();

        foreach ($normalizedQuantifiedAssociationsToMerge as $associationTypeCode => $association) {
            foreach ($association as $associationEntityType => $quantifiedLinks) {
                if (!isset($this->quantifiedAssociations[$associationTypeCode][$associationEntityType])) {
                    $this->quantifiedAssociations[$associationTypeCode][$associationEntityType] = [];
                }

                foreach ($quantifiedLinks as $quantifiedLink) {
                    $key = $this->searchKeyOfDuplicatedQuantifiedAssociation(
                        $this->quantifiedAssociations,
                        $associationTypeCode,
                        $associationEntityType,
                        $quantifiedLink
                    );

                    if (null !== $key) {
                        $this->quantifiedAssociations[$associationTypeCode][$associationEntityType][$key]['quantity'] = $quantifiedLink['quantity'];
                        continue;
                    }

                    $this->quantifiedAssociations[$associationTypeCode][$associationEntityType][] = $quantifiedLink;
                }
            }
        }
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
                    var_dump($quantifiedLink);
                    $result[$associationType][$quantifiedLinksType][] = $quantifiedLink;
                }
            }
        }

        return $result;
    }

    /**
     * Since we are using an unindexed array for the quantified associations,
     * we need to find if there is a row with the same identifier as the one we have.
     * With its key, we will be able to overwrite the quantity.
     *
     * For context, this is the structure:
     * [
     *      'PACK' => [
     *          'products' => [
     *              ['identifier' => 'foo', 'quantity' => 2],
     *              ['identifier' => 'bar', 'quantity' => 4],
     *          ]
     *      ]
     * ]
     *
     */
    private function searchKeyOfDuplicatedQuantifiedAssociation(
        array $source,
        string $associationTypeCode,
        string $associationEntityType,
        array $quantifiedLink
    ): ?int {
        $matchingSourceQuantifiedAssociations = array_filter(
            $source[$associationTypeCode][$associationEntityType] ?? [],
            function ($sourceQuantifiedAssociation) use ($quantifiedLink) {
                return $sourceQuantifiedAssociation['identifier'] === $quantifiedLink['identifier'];
            }
        );

        if (empty($matchingSourceQuantifiedAssociations)) {
            return null;
        }

        return array_keys($matchingSourceQuantifiedAssociations)[0];
    }
}
