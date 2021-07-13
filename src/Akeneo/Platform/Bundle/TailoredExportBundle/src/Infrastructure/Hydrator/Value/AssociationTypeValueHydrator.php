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

use Akeneo\Pim\Enrichment\Component\Product\Model\GroupInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Platform\TailoredExport\Application\Query\Source\AssociationTypeSource;
use Akeneo\Platform\TailoredExport\Domain\SourceValue\SimpleAssociationsValue;
use Akeneo\Platform\TailoredExport\Domain\SourceValueInterface;

final class AssociationTypeValueHydrator
{
    public function hydrateFromSource(ProductInterface $product, AssociationTypeSource $source): SourceValueInterface
    {
        $associationTypeCode = $source->getCode();
        if ($source->isQuantified()) {
            throw new \LogicException('Unsupported quantified association type');
        }

        return new SimpleAssociationsValue(
            $this->getAssociatedProductIdentifiers($product, $associationTypeCode),
            $this->getAssociatedProductModelCodes($product, $associationTypeCode),
            $this->getAssociatedGroupCodes($product, $associationTypeCode)
        );

    }

    private function getAssociatedProductIdentifiers(ProductInterface $product, string $associationTypeCode): array
    {
        /* TODO: In another PR: add getAssociatedProductIdentifiers in Product/ProductModel/PublishedProduct to avoid manipulating Doctrine collection here */
        $associatedProducts = $product->getAssociatedProducts($associationTypeCode);
        $associatedProductIdentifiers = [];

        if ($associatedProducts) {
            $associatedProductIdentifiers = $associatedProducts->map(
                static fn(ProductInterface $associatedProduct): string => $associatedProduct->getIdentifier()
            )->getValues();
        }

        return $associatedProductIdentifiers;
    }

    private function getAssociatedProductModelCodes(ProductInterface $product, string $associationTypeCode): array
    {
        /* TODO: In another PR: add getAssociatedProductModelCodes in Product/ProductModel/PublishedProduct to avoid manipulating Doctrine collection here */
        $associatedProductModels = $product->getAssociatedProductModels($associationTypeCode);
        $associatedProductModelCodes = [];

        if ($associatedProductModels) {
            $associatedProductModelCodes = $associatedProductModels->map(
                static fn(ProductModelInterface $associatedProductModel): string => $associatedProductModel->getCode()
            )->getValues();
        }

        return $associatedProductModelCodes;
    }

    private function getAssociatedGroupCodes(ProductInterface $product, string $associationTypeCode): array
    {
        /* TODO: In another PR: add getAssociatedGroupCodes in Product/ProductModel/PublishedProduct to avoid manipulating Doctrine collection here */
        $associatedGroups = $product->getAssociatedGroups($associationTypeCode);
        $associatedGroupCodes = [];

        if ($associatedGroups) {
            $associatedGroupCodes = $associatedGroups->map(
                static fn(GroupInterface $associationGroup): string => $associationGroup->getCode()
            )->getValues();
        }

        return $associatedGroupCodes;
    }
}
