<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredExport\Infrastructure\Hydrator\Value;

use Akeneo\Pim\Enrichment\Component\Product\Model\AssociationInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\GroupInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Platform\TailoredExport\Application\Common\Source\AssociationTypeSource;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\QuantifiedAssociation;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\QuantifiedAssociationsValue;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\SimpleAssociationsValue;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\SourceValueInterface;

final class AssociationTypeValueHydrator
{
    public function hydrate(ProductInterface $product, string $associationTypeCode, bool $isQuantified): SourceValueInterface
    {
        if ($isQuantified) {
            $normalizedQuantifiedAssociations = $product->getQuantifiedAssociations()->normalize()[$associationTypeCode] ?? [];

            return new QuantifiedAssociationsValue(
                $this->getProductQuantifiedAssociations($normalizedQuantifiedAssociations),
                $this->getProductModelQuantifiedAssociations($normalizedQuantifiedAssociations),
            );
        }

        return new SimpleAssociationsValue(
            $this->getAssociatedProductIdentifiers($product, $associationTypeCode),
            $this->getAssociatedProductModelCodes($product, $associationTypeCode),
            $this->getAssociatedGroupCodes($product, $associationTypeCode),
        );
    }

    private function getAssociatedProductIdentifiers(ProductInterface $product, string $associationTypeCode): array
    {
        $association = $this->getAssociationForTypeCode($product, $associationTypeCode);
        $associatedProducts = $association ? $association->getProducts()->toArray() : [];

        return array_map(
            static fn (ProductInterface $associatedProduct): string => $associatedProduct->getIdentifier(),
            $associatedProducts,
        );
    }

    private function getAssociatedProductModelCodes(ProductInterface $product, string $associationTypeCode): array
    {
        $association = $this->getAssociationForTypeCode($product, $associationTypeCode);
        $associatedProductModels = $association ? $association->getProductModels()->toArray() : [];

        return array_map(
            static fn (ProductModelInterface $associatedProductModel): string => $associatedProductModel->getCode(),
            $associatedProductModels,
        );
    }

    private function getAssociatedGroupCodes(ProductInterface $product, string $associationTypeCode): array
    {
        $association = $this->getAssociationForTypeCode($product, $associationTypeCode);
        $associatedGroups = $association ? $association->getGroups()->toArray() : [];

        return array_map(
            static fn (GroupInterface $associationGroup): string => $associationGroup->getCode(),
            $associatedGroups,
        );
    }

    private function getAssociationForTypeCode(ProductInterface $product, string $typeCode): ?AssociationInterface
    {
        foreach ($product->getAllAssociations() as $association) {
            if ($association->getAssociationType()->getCode() === $typeCode) {
                return $association;
            }
        }

        return null;
    }

    /**
     * @param array $normalizedQuantifiedAssociations = [
     *     'products' => array<{identifier': string, "quantity": int}>,
     *     'product_models' => array<{identifier': string, "quantity": int}>
     * ]
     *
     * @return QuantifiedAssociation[]
     */
    private function getProductQuantifiedAssociations(array $normalizedQuantifiedAssociations): array
    {
        $normalizedProductQuantifiedAssociations = $normalizedQuantifiedAssociations['products'] ?? [];

        return array_map(
            static fn ($productQuantifiedAssociation) => new QuantifiedAssociation(
                $productQuantifiedAssociation['identifier'],
                $productQuantifiedAssociation['quantity'],
            ),
            $normalizedProductQuantifiedAssociations
        );
    }

    /**
     * @param array $normalizedQuantifiedAssociations = [
     *     'products' => array<{identifier': string, "quantity": int}>,
     *     'product_models' => array<{identifier': string, "quantity": int}>
     * ]
     *
     * @return QuantifiedAssociation[]
     */
    private function getProductModelQuantifiedAssociations(array $normalizedQuantifiedAssociations): array
    {
        $normalizedProductModelQuantifiedAssociations = $normalizedQuantifiedAssociations['product_models'] ?? [];

        return array_map(
            static fn ($productQuantifiedAssociation) => new QuantifiedAssociation(
                $productQuantifiedAssociation['identifier'],
                $productQuantifiedAssociation['quantity'],
            ),
            $normalizedProductModelQuantifiedAssociations
        );
    }
}
