<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Permission\Bundle\Enrichment\Storage\Sql\Product;

use Akeneo\Channel\API\Query\FindAllViewableLocalesForUser;
use Akeneo\Channel\API\Query\Locale;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProduct;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProductList;
use Akeneo\Pim\Enrichment\Component\Product\Exception\ObjectNotFoundException;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetConnectorProducts;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Akeneo\Pim\Permission\Bundle\Enrichment\Storage\Sql\Category\GetViewableCategoryCodes;
use Akeneo\Pim\Permission\Bundle\Enrichment\Storage\Sql\ProductModel\FetchUserRightsOnProductModel;
use Akeneo\Pim\Permission\Component\Authorization\Model\UserRightsOnProduct;
use Akeneo\Pim\Permission\Component\Authorization\Model\UserRightsOnProductModel;
use Akeneo\Pim\Permission\Component\Authorization\Model\UserRightsOnProductUuid;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Permission\GetViewableAttributeCodesForUserInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Query\PublicApi\GetWorkflowStatusFromProductIdentifiers;
use Ramsey\Uuid\UuidInterface;

/**
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class SqlGetConnectorProductsWithPermissions implements GetConnectorProducts
{
    public function __construct(
        private GetConnectorProducts $getConnectorProducts,
        private GetViewableCategoryCodes $getViewableCategoryCodes,
        private GetViewableAttributeCodesForUserInterface $getViewableAttributeCodesForUser,
        private FindAllViewableLocalesForUser $getViewableLocaleCodesForUser,
        private FetchUserRightsOnProduct $fetchUserRightsOnProduct,
        private FetchUserRightsOnProductModel $fetchUserRightsOnProductModel,
        private GetWorkflowStatusFromProductIdentifiers $getWorkflowStatusFromProductIdentifiers
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function fromProductQueryBuilder(
        ProductQueryBuilderInterface $pqb,
        int $userId,
        ?array $attributesToFilterOn,
        ?string $channelToFilterOn,
        ?array $localesToFilterOn
    ): ConnectorProductList {
        $connectorProductList = $this->getConnectorProducts->fromProductQueryBuilder(
            $pqb,
            $userId,
            $attributesToFilterOn,
            $channelToFilterOn,
            $localesToFilterOn
        );

        $productsWithoutPermissionApplied = $connectorProductList->connectorProducts();
        $productsWithPermissionApplied = $this->fromConnectorProductsWithoutPermission($productsWithoutPermissionApplied, $userId);

        return new ConnectorProductList($connectorProductList->totalNumberOfProducts(), $productsWithPermissionApplied);
    }

    public function fromProductUuid(UuidInterface $productUuid, int $userId): ConnectorProduct
    {
        $userRights = $this->fetchUserRightsOnProduct->fetchByUuid($productUuid, $userId);
        if (!$userRights->isProductViewable()) {
            throw new ObjectNotFoundException(sprintf('Product "%s" is not viewable by user id "%s".', $productUuid->toString(), $userId));
        }

        $productWithoutPermissionApplied = $this->getConnectorProducts->fromProductUuid($productUuid, $userId);

        return $this->fromConnectorProductsWithoutPermission([$productWithoutPermissionApplied], $userId)[0];
    }

    public function fromProductUuids(
        array $productUuids,
        int $userId,
        ?array $attributesToFilterOn,
        ?string $channelToFilterOn,
        ?array $localesToFilterOn
    ): ConnectorProductList {
        $viewableProductUuids = $this->filterViewableProductUuids(\array_values($productUuids), $userId);

        $connectorProductList = $this->getConnectorProducts->fromProductUuids(
            $viewableProductUuids,
            $userId,
            $attributesToFilterOn,
            $channelToFilterOn,
            $localesToFilterOn
        );

        $productsWithPermissionApplied = $this->fromConnectorProductsWithoutPermission($connectorProductList->connectorProducts(), $userId);

        return new ConnectorProductList($connectorProductList->totalNumberOfProducts(), $productsWithPermissionApplied);
    }

    private function fromConnectorProductsWithoutPermission(array $products, int $userId): array
    {
        $filteredProducts = $this->filterNotGrantedCategoryCodes($products, $userId);
        $filteredProducts = $this->filterNotGrantedAttributeAndLocalesCodes($filteredProducts, $userId);
        $filteredProducts = $this->filterNotGrantedAssociatedProducts($filteredProducts, $userId);
        $filteredProducts = $this->filterNotGrantedAssociatedWithQuantityProducts($filteredProducts, $userId);
        $filteredProducts = $this->filterNotGrantedAssociatedProductModels($filteredProducts, $userId);
        $filteredProducts = $this->filterNotGrantedAssociatedWithQuantityProductModels($filteredProducts, $userId);

        return $this->addWorkflowStatusInMetadata($filteredProducts, $userId);
    }

    private function filterNotGrantedCategoryCodes(array $products, int $userId): array
    {
        $categoryCodes = [];
        foreach ($products as $product) {
            $categoryCodes[] = $product->categoryCodes();
        }
        $categoryCodes = !empty($categoryCodes) ? array_unique(array_merge(...$categoryCodes)) : [];

        $grantedCategoryCodes = $this->getViewableCategoryCodes->forCategoryCodes($userId, $categoryCodes);

        return array_map(function (ConnectorProduct $product) use ($grantedCategoryCodes) {
            return $product->filterByCategoryCodes($grantedCategoryCodes);
        }, $products);
    }

    private function filterNotGrantedAttributeAndLocalesCodes(array $products, int $userId): array
    {
        $attributeCodes = [];
        foreach ($products as $product) {
            $attributeCodes[] = $product->attributeCodesInValues();
        }
        $attributeCodes = !empty($attributeCodes) ? array_unique(array_merge(...$attributeCodes)) : [];

        $grantedAttributeCodes = $this->getViewableAttributeCodesForUser->forAttributeCodes($attributeCodes, $userId);
        $grantedLocaleCodes = \array_map(
            static fn (Locale $locale): string => $locale->getCode(),
            $this->getViewableLocaleCodesForUser->findAll($userId)
        );

        return array_map(function (ConnectorProduct $product) use ($grantedAttributeCodes, $grantedLocaleCodes) {
            return $product->filterValuesByAttributeCodesAndLocaleCodes($grantedAttributeCodes, $grantedLocaleCodes);
        }, $products);
    }

    private function filterNotGrantedAssociatedProducts(array $products, int $userId): array
    {
        $productIdentifiers = [];
        foreach ($products as $product) {
            $productIdentifiers[] = $product->associatedProductIdentifiers();
        }

        $productIdentifiers = !empty($productIdentifiers) ? array_unique(array_merge(...$productIdentifiers)) : [];
        $viewableAssociatedProductIdentifiers = $this->filterViewableProductIdentifiers($productIdentifiers, $userId);

        return array_map(function (ConnectorProduct $product) use ($viewableAssociatedProductIdentifiers) {
            return $product->filterAssociatedProductsByProductIdentifiers($viewableAssociatedProductIdentifiers);
        }, $products);
    }

    private function filterNotGrantedAssociatedProductModels(array $products, int $userId): array
    {
        $productModelCodes = [];
        foreach ($products as $product) {
            $productModelCodes[] = $product->associatedProductModelCodes();
        }

        $productModelCodes = !empty($productModelCodes) ? array_unique(array_merge(...$productModelCodes)) : [];
        $viewableAssociatedProductModelCodes = $this->filterViewableProductModelCodes($productModelCodes, $userId);

        return array_map(function (ConnectorProduct $product) use ($viewableAssociatedProductModelCodes) {
            return $product->filterAssociatedProductModelsByProductModelCodes($viewableAssociatedProductModelCodes);
        }, $products);
    }

    private function filterNotGrantedAssociatedWithQuantityProducts(array $products, int $userId): array
    {
        $productIdentifiers = array_map(function (ConnectorProduct $product) {
            return $product->associatedWithQuantityProductIdentifiers();
        }, $products);

        $productIdentifiers = !empty($productIdentifiers) ? array_unique(array_merge(...$productIdentifiers)) : [];
        $viewableAssociatedProductIdentifiers = $this->filterViewableProductIdentifiers($productIdentifiers, $userId);

        return array_map(function (ConnectorProduct $product) use ($viewableAssociatedProductIdentifiers) {
            return $product->filterAssociatedWithQuantityProductsByProductIdentifiers($viewableAssociatedProductIdentifiers);
        }, $products);
    }

    private function filterNotGrantedAssociatedWithQuantityProductModels(array $products, int $userId): array
    {
        $productModelCodes = array_map(function (ConnectorProduct $product) {
            return $product->associatedWithQuantityProductModelCodes();
        }, $products);

        $productModelCodes = !empty($productModelCodes) ? array_unique(array_merge(...$productModelCodes)) : [];
        $viewableAssociatedProductModelCodes = $this->filterViewableProductModelCodes($productModelCodes, $userId);

        return array_map(function (ConnectorProduct $product) use ($viewableAssociatedProductModelCodes) {
            return $product->filterAssociatedWithQuantityProductModelsByProductModelCodes($viewableAssociatedProductModelCodes);
        }, $products);
    }

    private function addWorkflowStatusInMetadata(array $products, int $userId): array
    {
        $productIdentifiers = array_map(function (ConnectorProduct $connectorProduct) {
            return $connectorProduct->identifier();
        }, $products);

        $workflowStatusIndexedByProductIdentifiers = $this->getWorkflowStatusFromProductIdentifiers->fromProductIdentifiers($productIdentifiers, $userId);

        return array_map(function (ConnectorProduct $connectorProduct) use ($workflowStatusIndexedByProductIdentifiers) {
            if (null !== $workflowStatus = $workflowStatusIndexedByProductIdentifiers[$connectorProduct->identifier()] ?? null) {
                return $connectorProduct->addMetadata('workflow_status', $workflowStatus);
            }

            return $connectorProduct;
        }, $products);
    }

    private function filterViewableProductIdentifiers(array $productIdentifiers, int $userId): array
    {
        $productRights = $this->fetchUserRightsOnProduct->fetchByIdentifiers($productIdentifiers, $userId);
        $viewableAssociatedProducts = array_filter($productRights, function (UserRightsOnProduct $productRight) {
            return $productRight->isProductViewable();
        });

        return array_map(function (UserRightsOnProduct $productRight) {
            return $productRight->productIdentifier();
        }, $viewableAssociatedProducts);
    }

    private function filterViewableProductUuids(array $productUuids, int $userId): array
    {
        $productRights = $this->fetchUserRightsOnProduct->fetchByUuids($productUuids, $userId);
        $viewableAssociatedProducts = array_filter($productRights, function (UserRightsOnProductUuid $productRight) {
            return $productRight->isProductViewable();
        });

        return array_map(function (UserRightsOnProductUuid $productRight) {
            return $productRight->productUuid();
        }, $viewableAssociatedProducts);
    }

    private function filterViewableProductModelCodes(array $productModelCodes, int $userId): array
    {
        $productModelRights = $this->fetchUserRightsOnProductModel->fetchByIdentifiers($productModelCodes, $userId);
        $viewableAssociatedProductModels = array_filter($productModelRights, function (UserRightsOnProductModel $productModelRight) {
            return $productModelRight->isProductModelViewable();
        });

        return array_map(function (UserRightsOnProductModel $productModelRight) {
            return $productModelRight->productModelCode();
        }, $viewableAssociatedProductModels);
    }
}
