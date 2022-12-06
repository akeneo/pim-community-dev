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
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyVariantInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\GroupInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\QuantifiedAssociation;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\QuantifiedAssociationsValue;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\SimpleAssociationsValue;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\SourceValueInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @phpstan-type NormalizedProducts array{identifier: string, quantity: int}
 * @phpstan-type NormalizedProductModels array{identifier: string, quantity: int}
 * @phpstan-type NormalizedAssociation array{products: NormalizedProducts, product_models: NormalizedProductModels}
 */
final class AssociationTypeValueHydrator
{
    public function __construct(
        private readonly NormalizerInterface $normalizer,
    ) {
    }

    public function hydrate(
        ProductInterface|ProductModelInterface $productOrProductModel,
        string $associationTypeCode,
        bool $isQuantified,
    ): SourceValueInterface {
        if ($isQuantified) {
            /** @var NormalizedAssociation[] $normalizedQuantifiedAssociations */
            $normalizedQuantifiedAssociations = $this->normalizer->normalize($productOrProductModel, 'standard')['quantified_associations'][$associationTypeCode] ?? [];

            return new QuantifiedAssociationsValue(
                $this->getProductQuantifiedAssociations($normalizedQuantifiedAssociations),
                $this->getProductModelQuantifiedAssociations($normalizedQuantifiedAssociations),
            );
        }

        return new SimpleAssociationsValue(
            $this->getAssociatedProductIdentifiers($productOrProductModel, $associationTypeCode),
            $this->getAssociatedProductModelCodes($productOrProductModel, $associationTypeCode),
            $this->getAssociatedGroupCodes($productOrProductModel, $associationTypeCode),
        );
    }

    private function getAssociatedProductIdentifiers(
        ProductInterface|ProductModelInterface $productOrProductModel,
        string $associationTypeCode,
    ): array {
        $association = $this->getAssociationForTypeCode($productOrProductModel, $associationTypeCode);
        $associatedProducts = null !== $association ? $association->getProducts()->toArray() : [];

        return array_map(
            static fn (ProductInterface $associatedProduct): string => $associatedProduct->getIdentifier(),
            $associatedProducts,
        );
    }

    private function getAssociatedProductModelCodes(
        ProductInterface|ProductModelInterface $productOrProductModel,
        string $associationTypeCode,
    ): array {
        $association = $this->getAssociationForTypeCode($productOrProductModel, $associationTypeCode);
        $associatedProductModels = null !== $association ? $association->getProductModels()->toArray() : [];

        return array_map(
            static fn (ProductModelInterface $associatedProductModel): string => $associatedProductModel->getCode(),
            $associatedProductModels,
        );
    }

    private function getAssociatedGroupCodes(
        ProductInterface|ProductModelInterface $productOrProductModel,
        string $associationTypeCode,
    ): array {
        $association = $this->getAssociationForTypeCode($productOrProductModel, $associationTypeCode);
        $associatedGroups = null !== $association ? $association->getGroups()->toArray() : [];

        return array_map(
            static fn (GroupInterface $associationGroup): string => $associationGroup->getCode(),
            $associatedGroups,
        );
    }

    private function getAssociationForTypeCode(
        ProductInterface|ProductModelInterface $productOrProductModel,
        string $typeCode,
    ): ?AssociationInterface {
        foreach ($productOrProductModel->getAllAssociations() as $association) {
            if ($association->getAssociationType()->getCode() === $typeCode) {
                return $association;
            }
        }

        return null;
    }

    /**
     * @param NormalizedAssociation[] $normalizedQuantifiedAssociations
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
            $normalizedProductQuantifiedAssociations,
        );
    }

    /**
     * @param NormalizedAssociation[] $normalizedQuantifiedAssociations
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
            $normalizedProductModelQuantifiedAssociations,
        );
    }

    private function getAncestors(EntityWithFamilyVariantInterface $entity): array
    {
        $ancestors = [];
        $current = $entity;

        while (null !== $parent = $current->getParent()) {
            $current = $parent;
            $ancestors[] = $current;
        }

        return array_reverse($ancestors);
    }
}
