<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Query\Product;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MapQuantifiedAssociation
{
    /**
     * @var MapProductWithPermissions
     */
    private $mapProduct;
    /**
     * @var MapProductModelWithPermissions
     */
    private $mapProductModel;

    public function __construct(
        MapProductWithPermissions $mapProduct,
        MapProductModelWithPermissions $mapProductModel
    ) {
        $this->mapProduct = $mapProduct;
        $this->mapProductModel = $mapProductModel;
    }

    public function mapToIdentifiersAndCodes(array $quantifiedAssociationWithIds): array
    {
        $productIds = $this->getAllLinkedProductIds($quantifiedAssociationWithIds);
        $productIdentifiers = $this->mapProduct->forIds($productIds);
        $productModelIds = $this->getAllLinkedProductModelIds($quantifiedAssociationWithIds);
        $productModelCodes = $this->mapProductModel->forIds($productModelIds);

        $quantifiedAssociationsWithIdentifiersAndCodes = [];
        foreach ($quantifiedAssociationWithIds as $associationTypeCode => $quantifiedAssociation) {
            $quantifiedAssociationsWithIdentifiersAndCodes[$associationTypeCode]['products'] = array_map(function ($association) use ($productIdentifiers) {
                return ['identifier' => $productIdentifiers[$association['id']], 'quantity' => $association['quantity']];
            }, $quantifiedAssociation['products'] ?? []);
            $quantifiedAssociationsWithIdentifiersAndCodes[$associationTypeCode]['product_models'] = array_map(function ($association) use ($productModelCodes) {
                return ['code' => $productModelCodes[$association['id']], 'quantity' => $association['quantity']];
            }, $quantifiedAssociation['product_models'] ?? []);
        }

        return $quantifiedAssociationsWithIdentifiersAndCodes;
    }

    public function getQuantifiedAssociationsWithIds(array $quantifiedAssociationsWithIdentifiers): array
    {
        $productIdentifiers = $this->getAllLinkedProductIdentifiers($quantifiedAssociationsWithIdentifiers);
        $productIds = $this->mapProduct->forIdentifiers($productIdentifiers);
        $productModelCodes = $this->getAllLinkedProductModelIdentifiers($quantifiedAssociationsWithIdentifiers);
        $productModelIds = $this->mapProductModel->forCodes($productModelCodes);

        $quantifiedAssociations = [];
        foreach ($quantifiedAssociationsWithIdentifiers as $associationTypeCode => $quantifiedAssociation) {
            $quantifiedAssociations[$associationTypeCode]['products'] = array_map(function ($association) use ($productIds) {
                return ['id' => $productIds[$association['identifier']], 'quantity' => $association['quantity']];
            }, $quantifiedAssociationsWithIdentifiers[$associationTypeCode]['products'] ?? []);
            $quantifiedAssociations[$associationTypeCode]['product_models'] = array_map(function ($association) use ($productModelIds) {
                return ['id' => $productModelIds[$association['code']], 'quantity' => $association['quantity']];
            }, $quantifiedAssociationsWithIdentifiers[$associationTypeCode]['product_models'] ?? []);
        }

        return $quantifiedAssociations;
    }

    protected function getAllLinkedProductIds(array $quantifiedAssociations)
    {
        return array_reduce($quantifiedAssociations, function (array $carry, array $quantifiedAssociation) {
            return array_merge($carry, array_column($quantifiedAssociation['products'] ?? [], 'id'));
        }, []);
    }

    protected function getAllLinkedProductModelIds(array $quantifiedAssociations)
    {
        return array_reduce($quantifiedAssociations, function (array $carry, array $quantifiedAssociation) {
            return array_merge($carry, array_column($quantifiedAssociation['product_models'] ?? [], 'id'));
        }, []);
    }

    protected function getAllLinkedProductIdentifiers(array $quantifiedAssociations)
    {
        return array_reduce($quantifiedAssociations, function (array $carry, array $quantifiedAssociation) {
            return array_merge($carry, array_column($quantifiedAssociation['products'] ?? [], 'identifier'));
        }, []);
    }

    protected function getAllLinkedProductModelIdentifiers(array $quantifiedAssociations)
    {
        return array_reduce($quantifiedAssociations, function (array $carry, array $quantifiedAssociation) {
            return array_merge($carry, array_column($quantifiedAssociation['product_models'] ?? [], 'identifier'));
        }, []);
    }

}
