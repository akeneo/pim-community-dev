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

namespace Akeneo\Pim\Permission\Bundle\Enrichment\Storage\Sql\ProductModel;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProductModelList;
use Akeneo\Pim\Enrichment\Component\Product\ProductModel\Query\GetConnectorProductModels;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Akeneo\Pim\Permission\Bundle\Enrichment\Storage\Sql\Category\GetViewableCategoryCodes;
use Akeneo\Pim\Permission\Bundle\Enrichment\Storage\Sql\Product\FetchUserRightsOnProduct;
use Akeneo\Pim\Permission\Bundle\Persistence\Sql\GetViewableAttributeCodesForUser;
use Akeneo\Pim\Permission\Component\Query\GetAllViewableLocalesForUser;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Query\PublicApi\GetWorkflowStatusFromProductModelCodes;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class SqlGetConnectorProductModelsWithPermissions implements GetConnectorProductModels
{
    /** @var GetConnectorProductModels */
    private $getConnectorProductModels;

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

    /** @var GetWorkflowStatusFromProductModelCodes */
    private $getWorkflowStatusFromProductModelCodes;

    public function __construct(
        GetConnectorProductModels $getConnectorProductModels,
        GetViewableCategoryCodes $getViewableCategoryCodes,
        GetViewableAttributeCodesForUser $getViewableAttributeCodesForUser,
        GetAllViewableLocalesForUser $getViewableLocaleCodesForUser,
        FetchUserRightsOnProduct $fetchUserRightsOnProduct,
        FetchUserRightsOnProductModel $fetchUserRightsOnProductModel,
        GetWorkflowStatusFromProductModelCodes $getWorkflowStatusFromProductModelCodes
    ) {
        $this->getConnectorProductModels = $getConnectorProductModels;
        $this->getViewableCategoryCodes = $getViewableCategoryCodes;
        $this->getViewableAttributeCodesForUser = $getViewableAttributeCodesForUser;
        $this->getViewableLocaleCodesForUser = $getViewableLocaleCodesForUser;
        $this->fetchUserRightsOnProduct = $fetchUserRightsOnProduct;
        $this->fetchUserRightsOnProductModel = $fetchUserRightsOnProductModel;
        $this->getWorkflowStatusFromProductModelCodes = $getWorkflowStatusFromProductModelCodes;
    }

    /**
     * {@inheritdoc}
     */
    public function fromProductQueryBuilder(
        ProductQueryBuilderInterface $productQueryBuilder,
        int $userId,
        ?array $attributesToFilterOn,
        ?string $channelToFilterOn,
        ?array $localesToFilterOn
    ): ConnectorProductModelList {
        $connectorProductModelList = $this->getConnectorProductModels->fromProductQueryBuilder(
            $productQueryBuilder,
            $userId,
            $attributesToFilterOn,
            $channelToFilterOn,
            $localesToFilterOn
        );
        $productModels = $connectorProductModelList->connectorProductModels();
        $filteredProductModels = $this->filterNotGrantedCategoryCodes($productModels, $userId);
        $filteredProductModels = $this->filterNotGrantedAttributeAndLocalesCodes($filteredProductModels, $userId);
        $filteredProductModels = $this->filterNotGrantedAssociatedProducts($filteredProductModels, $userId);
        $filteredProductModels = $this->filterNotGrantedAssociatedProductModels($filteredProductModels, $userId);
        $filteredProductModels = $this->addWorkflowStatusInMetadata($filteredProductModels, $userId);

        return new ConnectorProductModelList(
            $connectorProductModelList->totalNumberOfProductModels(), $filteredProductModels
        );
    }

    private function filterNotGrantedCategoryCodes(array $productModels, int $userId): array
    {
        $categoryCodes = [];
        foreach ($productModels as $productModel) {
            $categoryCodes[] = $productModel->categoryCodes();
        }
        $categoryCodes = !empty($categoryCodes) ? array_unique(array_merge(...$categoryCodes)) : [];
        $grantedCategoryCodes = $this->getViewableCategoryCodes->forCategoryCodes($userId, $categoryCodes);

        return array_map(
            function (ConnectorProductModel $productModel) use ($grantedCategoryCodes) {
                return $productModel->filterByCategoryCodes($grantedCategoryCodes);
            },
            $productModels
        );
    }

    private function filterNotGrantedAttributeAndLocalesCodes(array $productModels, int $userId): array
    {
        $attributeCodes = [];
        foreach ($productModels as $productModel) {
            $attributeCodes[] = $productModel->attributeCodesInValues();
        }
        $attributeCodes = !empty($attributeCodes) ? array_unique(array_merge(...$attributeCodes)) : [];
        $grantedAttributeCodes = $this->getViewableAttributeCodesForUser->forAttributeCodes($attributeCodes, $userId);
        $grantedLocaleCodes = $this->getViewableLocaleCodesForUser->fetchAll($userId);

        return array_map(
            function (ConnectorProductModel $productModel) use ($grantedAttributeCodes, $grantedLocaleCodes) {
                return $productModel->filterValuesByAttributeCodesAndLocaleCodes(
                    $grantedAttributeCodes,
                    $grantedLocaleCodes
                );
            },
            $productModels
        );
    }

    private function filterNotGrantedAssociatedProducts(array $productModels, $userId): array
    {
        $productIdentifiers = [];
        foreach ($productModels as $productModel) {
            $productIdentifiers[] = $productModel->associatedProductIdentifiers();
        }
        $productIdentifiers = !empty($productIdentifiers) ? array_unique(array_merge(...$productIdentifiers)) : [];
        $viewableAssociatedProductIdentifiers = [];
        $productRights = $this->fetchUserRightsOnProduct->fetchByIdentifiers($productIdentifiers, $userId);
        foreach ($productRights as $productRight) {
            if ($productRight->isProductViewable()) {
                $viewableAssociatedProductIdentifiers[] = $productRight->productIdentifier();
            }
        }

        return array_map(
            function (ConnectorProductModel $productModel) use ($viewableAssociatedProductIdentifiers) {
                return $productModel->filterAssociatedProductsByProductIdentifiers(
                    $viewableAssociatedProductIdentifiers
                );
            },
            $productModels
        );
    }

    private function filterNotGrantedAssociatedProductModels(array $productModels, int $userId): array
    {
        $productModelCodes = [];
        foreach ($productModels as $productModel) {
            $productModelCodes[] = $productModel->associatedProductModelCodes();
        }
        $productModelCodes = !empty($productModelCodes) ? array_unique(array_merge(...$productModelCodes)) : [];
        $viewableAssociatedProductModelCodes = [];
        $productModelRights = $this->fetchUserRightsOnProductModel->fetchByIdentifiers($productModelCodes, $userId);
        foreach ($productModelRights as $productModelRight) {
            if ($productModelRight->isProductModelViewable()) {
                $viewableAssociatedProductModelCodes[] = $productModelRight->productModelCode();
            }
        }

        return array_map(
            function (ConnectorProductModel $productModel) use ($viewableAssociatedProductModelCodes) {
                return $productModel->filterAssociatedProductModelsByProductModelCodes(
                    $viewableAssociatedProductModelCodes
                );
            },
            $productModels
        );
    }

    private function addWorkflowStatusInMetadata(array $productModels, int $userId): array
    {
        $productModelCodes = $productIdentifiers = array_map(
            function (ConnectorProductModel $connectorProductModel) {
                return $connectorProductModel->code();
            },
            $productModels
        );
        $workflowStatuses = $this->getWorkflowStatusFromProductModelCodes->fromProductModelCodes(
            $productModelCodes,
            $userId
        );

        return array_map(
            function (ConnectorProductModel $productModel) use ($workflowStatuses) {
                return $productModel->addMetadata('workflow_status', $workflowStatuses[$productModel->code()]);
            },
            $productModels
        );
    }
}
