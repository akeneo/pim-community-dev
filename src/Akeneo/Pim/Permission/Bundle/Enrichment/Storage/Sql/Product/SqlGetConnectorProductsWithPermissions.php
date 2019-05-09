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

use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProduct;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProductList;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetConnectorProducts;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Akeneo\Pim\Permission\Bundle\Enrichment\Storage\Sql\Category\GetViewableCategoryCodes;
use Akeneo\Pim\Permission\Bundle\Enrichment\Storage\Sql\ProductModel\FetchUserRightsOnProductModel;
use Akeneo\Pim\Permission\Bundle\Persistence\Sql\GetViewableAttributeCodesForUser;
use Akeneo\Pim\Permission\Component\Query\GetAllViewableLocalesForUser;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Query\PublicApi\GetWorkflowStatusFromProductIdentifiers;

/**
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class SqlGetConnectorProductsWithPermissions implements GetConnectorProducts
{
    /** @var GetConnectorProducts */
    private $getConnectorProducts;

    /** @var GetViewableCategoryCodes */
    private $getViewableCategoryCodes;

    /** @var GetViewableAttributeCodesForUser */
    private $getViewableAttributeCodesForUser;

    /** @var GetAllViewableLocalesForUser */
    private $getViewableLocaleCodesForUser;

    /** @var FetchUserRightsOnProduct */
    private $fetchUserRightsOnProduct;

    /** @var FetchUserRightsOnProductModel */
    private $fetchUserRightsOnProductModel;

    /** @var GetWorkflowStatusFromProductIdentifiers */
    private $getWorkflowStatusFromProductIdentifiers;

    public function __construct(
        GetConnectorProducts $getConnectorProducts,
        GetViewableCategoryCodes $getViewableCategoryCodes,
        GetViewableAttributeCodesForUser $getViewableAttributeCodesForUser,
        GetAllViewableLocalesForUser $getViewableLocaleCodesForUser,
        FetchUserRightsOnProduct $fetchUserRightsOnProduct,
        FetchUserRightsOnProductModel $fetchUserRightsOnProductModel,
        GetWorkflowStatusFromProductIdentifiers $getWorkflowStatusFromProductIdentifiers
    ) {
        $this->getConnectorProducts = $getConnectorProducts;
        $this->getViewableCategoryCodes = $getViewableCategoryCodes;
        $this->getViewableAttributeCodesForUser = $getViewableAttributeCodesForUser;
        $this->getViewableLocaleCodesForUser = $getViewableLocaleCodesForUser;
        $this->fetchUserRightsOnProduct = $fetchUserRightsOnProduct;
        $this->fetchUserRightsOnProductModel = $fetchUserRightsOnProductModel;
        $this->getWorkflowStatusFromProductIdentifiers = $getWorkflowStatusFromProductIdentifiers;
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
            $pqb, $userId, $attributesToFilterOn, $channelToFilterOn, $localesToFilterOn
        );

        $products = $connectorProductList->connectorProducts();
        $filteredProducts = $this->filterNotGrantedCategoryCodes($products, $userId);
        $filteredProducts = $this->filterNotGrantedAttributeAndLocalesCodes($filteredProducts, $userId);
        $filteredProducts = $this->filterNotGrantedAssociatedProducts($filteredProducts, $userId);
        $filteredProducts = $this->filterNotGrantedAssociatedProductModels($filteredProducts, $userId);
        $filteredProducts = $this->addWorkflowStatusInMetadata($filteredProducts, $userId);

        return new ConnectorProductList($connectorProductList->totalNumberOfProducts(), $filteredProducts);
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
        $grantedLocaleCodes = $this->getViewableLocaleCodesForUser->fetchAll($userId);

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

        $viewableAssociatedProductIdentifiers = [];
        $productRights = $this->fetchUserRightsOnProduct->fetchByIdentifiers($productIdentifiers, $userId);
        foreach ($productRights as $productRight) {
            if ($productRight->isProductViewable()) {
                $viewableAssociatedProductIdentifiers[] = $productRight->productIdentifier();
            }
        }

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

        $viewableAssociatedProductModelCodes = [];
        $productModelRights = $this->fetchUserRightsOnProductModel->fetchByIdentifiers($productModelCodes, $userId);
        foreach ($productModelRights as $productModelRight) {
            if ($productModelRight->isProductModelViewable()) {
                $viewableAssociatedProductModelCodes[] = $productModelRight->productModelCode();
            }
        }

        return array_map(function (ConnectorProduct $product) use ($viewableAssociatedProductModelCodes) {
            return $product->filterAssociatedProductModelsByProductModelCodes($viewableAssociatedProductModelCodes);
        }, $products);
    }

    private function addWorkflowStatusInMetadata(array $products, int $userId): array
    {
        $productIdentifiers = array_map(function (ConnectorProduct $connectorProduct) {
            return $connectorProduct->identifier();
        }, $products);

        $workflowStatusIndexedByProductIdentifiers = $this->getWorkflowStatusFromProductIdentifiers->fromProductIdentifiers($productIdentifiers, $userId);

        return array_map(function (ConnectorProduct $connectorProduct) use ($workflowStatusIndexedByProductIdentifiers) {
            $workflowStatus = $workflowStatusIndexedByProductIdentifiers[$connectorProduct->identifier()];

            return $connectorProduct->addMetadata('workflow_status', $workflowStatus);
        }, $products);
    }
}
