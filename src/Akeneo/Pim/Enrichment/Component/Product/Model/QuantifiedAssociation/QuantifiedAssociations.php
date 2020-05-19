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

            foreach ($associations[self::PRODUCTS_QUANTIFIED_LINKS_KEY] ?? [] as $productAssociation) {
                $quantifiedLink = new QuantifiedLink(
                    $productAssociation['identifier'],
                    $productAssociation['quantity']
                );
                $mappedQuantifiedAssociations[$associationType][self::PRODUCTS_QUANTIFIED_LINKS_KEY][] = $quantifiedLink;
            }

            foreach ($associations[self::PRODUCT_MODELS_QUANTIFIED_LINKS_KEY] ?? [] as $productModelAssociation) {
                $quantifiedLink = new QuantifiedLink(
                    $productModelAssociation['identifier'],
                    $productModelAssociation['quantity']
                );
                $mappedQuantifiedAssociations[$associationType][self::PRODUCT_MODELS_QUANTIFIED_LINKS_KEY][] = $quantifiedLink;
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

                $quantifiedLink = new QuantifiedLink(
                    $mappedProductIds->getIdentifier($productAssociation['id']),
                    $productAssociation['quantity']
                );
                $mappedQuantifiedAssociations[$associationType][self::PRODUCTS_QUANTIFIED_LINKS_KEY][] = $quantifiedLink;
            }

            foreach ($associations[self::PRODUCT_MODELS_QUANTIFIED_LINKS_KEY] ?? [] as $productModelAssociation) {
                Assert::keyExists($productModelAssociation, 'id');
                Assert::keyExists($productModelAssociation, 'quantity');

                $quantifiedLink = new QuantifiedLink(
                    $mappedProductModelIds->getIdentifier($productModelAssociation['id']),
                    $productModelAssociation['quantity']
                );
                $mappedQuantifiedAssociations[$associationType][self::PRODUCT_MODELS_QUANTIFIED_LINKS_KEY][] = $quantifiedLink;
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

            /** @var QuantifiedLink $quantifiedLink */
            foreach ($associations[self::PRODUCTS_QUANTIFIED_LINKS_KEY] as $quantifiedLink) {
                $result[$associationType][self::PRODUCTS_QUANTIFIED_LINKS_KEY][] = $quantifiedLink->normalize();
            }

            /** @var QuantifiedLink $quantifiedLink */
            foreach ($associations[self::PRODUCT_MODELS_QUANTIFIED_LINKS_KEY] as $quantifiedLink) {
                $result[$associationType][self::PRODUCT_MODELS_QUANTIFIED_LINKS_KEY][] = $quantifiedLink->normalize();
            }
        }

        return $result;
    }
}
