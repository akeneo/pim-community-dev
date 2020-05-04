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

    /** * @var array */
    private $quantifiedAssociations;

    private function __construct(
        array $rawQuantifiedAssociations,
        IdMapping $mappedProductIds,
        IdMapping $mappedProductModelIds
    ) {
        $mappedQuantifiedAssociations = [];
        foreach ($rawQuantifiedAssociations as $associationType => $associations) {
            foreach ($associations[self::PRODUCTS_QUANTIFIED_LINKS_KEY] as $productAssociation) {
                // TODO: Extract in VO QuantifiedLink
                $quantifiedLink = [
                    'identifier' => $mappedProductIds->getIdentifier($productAssociation['id']),
                    'quantity'   => $productAssociation['quantity']
                ];
                $mappedQuantifiedAssociations[$associationType][self::PRODUCTS_QUANTIFIED_LINKS_KEY][] = $quantifiedLink;
            }
            foreach ($associations[self::PRODUCT_MODELS_QUANTIFIED_LINKS_KEY] as $productModelAssociation) {
                $quantifiedLink = [
                    'identifier' => $mappedProductModelIds->getIdentifier($productModelAssociation['id']),
                    'quantity'   => $productModelAssociation['quantity']
                ];
                $mappedQuantifiedAssociations[$associationType][self::PRODUCT_MODELS_QUANTIFIED_LINKS_KEY][] = $quantifiedLink;
            }
        }

        $this->quantifiedAssociations = $mappedQuantifiedAssociations;
    }

    public static function createWithAssociationsAndMapping(
        array $rawQuantifiedAssociations,
        IdMapping $mappedProductIds,
        IdMapping $mappedProductModelIds
    ): self {
        return new self(
            $rawQuantifiedAssociations,
            $mappedProductIds,
            $mappedProductModelIds
        );
    }

    public function getQuantifiedAssociationsProductIdentifiers(): array
    {
        $result = [];
        foreach ($this->quantifiedAssociations as $associationType => $associations) {
            foreach ($associations[self::PRODUCTS_QUANTIFIED_LINKS_KEY] as $productAssociations) {
                $result[] = $productAssociations['identifier'];
            }
        }

        return $result;
    }

    public function getQuantifiedAssociationsProductModelCodes(): array
    {
        $result = [];
        foreach ($this->quantifiedAssociations as $associationType => $associations) {
            foreach ($associations[self::PRODUCT_MODELS_QUANTIFIED_LINKS_KEY] as $productModelAssociations) {
                $result[] = $productModelAssociations['identifier'];
            }
        }

        return $result;
    }

    public function normalizeWithMapping(IdMapping $mappedProductIdentifiers, IdMapping $mappedProductModelIdentifiers)
    {
        $result = [];
        foreach ($this->quantifiedAssociations as $associationType => $associations) {
            foreach ($associations[self::PRODUCTS_QUANTIFIED_LINKS_KEY] as $productAssociation) {
                $quantifiedLink = [
                    'id' => $mappedProductIdentifiers->getId($productAssociation['identifier']),
                    'quantity'   => $productAssociation['quantity']
                ];
                $result[$associationType][self::PRODUCTS_QUANTIFIED_LINKS_KEY][] = $quantifiedLink;
            }
            foreach ($associations[self::PRODUCT_MODELS_QUANTIFIED_LINKS_KEY] as $productModelAssociation) {
                $quantifiedLink = [
                    'id' => $mappedProductModelIdentifiers->getId($productModelAssociation['identifier']),
                    'quantity'   => $productModelAssociation['quantity']
                ];
                $result[$associationType][self::PRODUCT_MODELS_QUANTIFIED_LINKS_KEY][] = $quantifiedLink;
            }
        }

        return $result;
    }
}
