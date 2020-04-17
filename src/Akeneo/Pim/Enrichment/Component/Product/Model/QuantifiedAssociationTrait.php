<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Model;


trait QuantifiedAssociationTrait
{
    public function setQuantifiedAssociations(array $quantifiedAssociations)
    {
        $this->quantifiedAssociations = $quantifiedAssociations;
    }

    public function getQuantifiedAssociations()
    {
        return $this->quantifiedAssociations;
    }

    public function getAllLinkedProductIds()
    {
        return array_reduce($this->quantifiedAssociations, function (array $carry, array $quantifiedAssociation) {
            return array_merge($carry, array_column($quantifiedAssociation['products'] ?? [], 'id'));
        }, []);
    }

    public function getAllLinkedProductModelIds()
    {
        return array_reduce($this->quantifiedAssociations, function (array $carry, array $quantifiedAssociation) {
            return array_merge($carry, array_column($quantifiedAssociation['product_models'] ?? [], 'id'));
        }, []);
    }

    public function getQuantifiedAssociationsWithIdentifiersAndCodes(array $productIdentifiers, array $productModelCodes): array
    {
        $quantifiedAssociationsWithIdentifiersAndCodes = [];
        foreach ($this->quantifiedAssociations as $associationTypeCode => $quantifiedAssociation) {
            $quantifiedAssociationsWithIdentifiersAndCodes[$associationTypeCode]['products'] = array_map(function ($association) use ($productIdentifiers) {
                return ['identifier' => $productIdentifiers[$association['id']], 'quantity' => $association['quantity']];
            }, $quantifiedAssociation['products'] ?? []);
            $quantifiedAssociationsWithIdentifiersAndCodes[$associationTypeCode]['product_models'] = array_map(function ($association) use ($productModelCodes) {
                return ['code' => $productModelCodes[$association['id']], 'quantity' => $association['quantity']];
            }, $quantifiedAssociation['product_models'] ?? []);
        }

        return $quantifiedAssociationsWithIdentifiersAndCodes;
    }

    public function setQuantifiedAssociationsWithIds(array $newQuantifiedAssociations, array $productIds, array $productModelIds)
    {
        $quantifiedAssociations = [];
        foreach ($newQuantifiedAssociations as $associationTypeCode => $quantifiedAssociation) {
            $quantifiedAssociations[$associationTypeCode]['products'] = array_map(function ($association) use ($productIds) {
                return ['id' => $productIds[$association['identifier']], 'quantity' => $association['quantity']];
            }, $newQuantifiedAssociations[$associationTypeCode]['products'] ?? []);
            $quantifiedAssociations[$associationTypeCode]['product_models'] = array_map(function ($association) use ($productModelIds) {
                return ['id' => $productModelIds[$association['code']], 'quantity' => $association['quantity']];
            }, $newQuantifiedAssociations[$associationTypeCode]['product_models'] ?? []);
        }

        $this->quantifiedAssociations = $quantifiedAssociations;
    }
}
