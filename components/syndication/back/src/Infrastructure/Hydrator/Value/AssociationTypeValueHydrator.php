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

namespace Akeneo\Platform\Syndication\Infrastructure\Hydrator\Value;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProduct;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Model\GroupInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\QuantifiedAssociation;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\QuantifiedAssociationsValue;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\SimpleAssociationsValue;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\SourceValueInterface;

final class AssociationTypeValueHydrator
{
    /**
     * @param ConnectorProduct|ProductModelInterface $productOrProductModel
     */
    public function hydrate(
        $productOrProductModel,
        string $associationTypeCode,
        bool $isQuantified
    ): SourceValueInterface {
        $this->checkProductOrProductModelEntity($productOrProductModel);

        if ($isQuantified) {
            $normalizedQuantifiedAssociations = $productOrProductModel->getQuantifiedAssociations()->normalize()[$associationTypeCode] ?? [];

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

    /**
     * @param ConnectorProduct|ProductModelInterface $productOrProductModel
     */
    private function getAssociatedProductIdentifiers($productOrProductModel, string $associationTypeCode): array
    {
        $this->checkProductOrProductModelEntity($productOrProductModel);

        $association = $this->getAssociationForTypeCode($productOrProductModel, $associationTypeCode);
        $associatedProducts = $association !== null ? $association['products'] : [];

        return array_map(
            static fn (ConnectorProduct $associatedProduct): string => $associatedProduct->identifier(),
            $associatedProducts,
        );
    }

    /**
     * @param ConnectorProduct|ProductModelInterface $productOrProductModel
     */
    private function getAssociatedProductModelCodes($productOrProductModel, string $associationTypeCode): array
    {
        $this->checkProductOrProductModelEntity($productOrProductModel);

        $association = $this->getAssociationForTypeCode($productOrProductModel, $associationTypeCode);
        $associatedProductModels = $association !== null ? $association['product_models'] : [];

        return array_map(
            static fn (ProductModelInterface $associatedProductModel): string => $associatedProductModel->getCode(),
            $associatedProductModels,
        );
    }

    /**
     * @param ConnectorProduct|ProductModelInterface $productOrProductModel
     */
    private function getAssociatedGroupCodes($productOrProductModel, string $associationTypeCode): array
    {
        $this->checkProductOrProductModelEntity($productOrProductModel);

        $association = $this->getAssociationForTypeCode($productOrProductModel, $associationTypeCode);
        $associatedGroups = $association !== null ? $association['groups'] : [];

        return array_map(
            static fn (GroupInterface $associationGroup): string => $associationGroup->getCode(),
            $associatedGroups,
        );
    }

    /**
     * @param ConnectorProduct|ConnectorProductModel $productOrProductModel
     */
    private function getAssociationForTypeCode($productOrProductModel, string $typeCode): ?array
    {
        $this->checkProductOrProductModelEntity($productOrProductModel);

        foreach ($productOrProductModel->associations() as $associationType => $association) {
            if ($associationType === $typeCode) {
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

    /**
     * @param ConnectorProduct|ConnectorProductModel $productOrProductModel
     */
    private function checkProductOrProductModelEntity($productOrProductModel): void
    {
        if (
            !$productOrProductModel instanceof ConnectorProduct
            && !$productOrProductModel instanceof ConnectorProductModel
        ) {
            throw new \InvalidArgumentException('Cannot hydrate this entity');
        }
    }
}
