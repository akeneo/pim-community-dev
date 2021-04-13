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
use Akeneo\Pim\Enrichment\Component\Product\Exception\ObjectNotFoundException;
use Akeneo\Pim\Enrichment\Component\Product\ProductModel\Query\GetConnectorProductModels;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Akeneo\Pim\Permission\Bundle\Enrichment\Storage\Sql\Category\GetViewableCategoryCodes;
use Akeneo\Pim\Permission\Bundle\Enrichment\Storage\Sql\Product\FetchUserRightsOnProduct;
use Akeneo\Pim\Permission\Component\Authorization\Model\UserRightsOnProduct;
use Akeneo\Pim\Permission\Component\Authorization\Model\UserRightsOnProductModel;
use Akeneo\Pim\Permission\Component\Query\GetAllViewableLocalesForUser;
use Akeneo\Pim\Permission\Component\Query\GetViewableAttributeCodesForUserInterface;
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

    /** @var GetViewableAttributeCodesForUserInterface */
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
        GetViewableAttributeCodesForUserInterface $getViewableAttributeCodesForUser,
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
        $filteredProductModels = $this->filterConnectorProductModelsGivenPermissions($productModels, $userId);

        return new ConnectorProductModelList(
            $connectorProductModelList->totalNumberOfProductModels(), $filteredProductModels
        );
    }

    /**
     * {@inheritdoc}
     */
    public function fromProductModelCode(string $productModelCode, int $userId): ConnectorProductModel
    {
        $connectorProductModel = $this->getConnectorProductModels->fromProductModelCode($productModelCode, $userId);

        $userRights = $this->fetchUserRightsOnProductModel->fetchByIdentifier($productModelCode, $userId);
        if (!$userRights->isProductModelViewable()) {
            throw new ObjectNotFoundException(sprintf('product model "%s" was not found', $productModelCode));
        }

        $filteredProductModels = $this->filterConnectorProductModelsGivenPermissions([$connectorProductModel], $userId);

        return $filteredProductModels[0];
    }

    public function fromProductModelCodes(
        array $productModelCodes,
        int $userId,
        ?array $attributesToFilterOn,
        ?string $channelToFilterOn,
        ?array $localesToFilterOn
    ): ConnectorProductModelList {
        $viewableProductModelsCodes = $this->filterViewableProductModelCodes($productModelCodes, $userId);

        $connectorProductModelList = $this->getConnectorProductModels->fromProductModelCodes(
            $viewableProductModelsCodes,
            $userId,
            $attributesToFilterOn,
            $channelToFilterOn,
            $localesToFilterOn
        );

        $productModels = $connectorProductModelList->connectorProductModels();

        $filteredProductModels = $this->filterConnectorProductModelsGivenPermissions($productModels, $userId);

        return new ConnectorProductModelList(
            $connectorProductModelList->totalNumberOfProductModels(), $filteredProductModels
        );
    }

    private function filterConnectorProductModelsGivenPermissions(array $connectorProductModels, int $userId): array
    {
        $filteredProductModels = $this->filterNotGrantedCategoryCodes($connectorProductModels, $userId);
        $filteredProductModels = $this->filterNotGrantedAttributeAndLocalesCodes($filteredProductModels, $userId);
        $filteredProductModels = $this->filterNotGrantedAssociatedProducts($filteredProductModels, $userId);
        $filteredProductModels = $this->filterNotGrantedAssociatedWithQuantityProducts($filteredProductModels, $userId);
        $filteredProductModels = $this->filterNotGrantedAssociatedProductModels($filteredProductModels, $userId);
        $filteredProductModels = $this->filterNotGrantedAssociatedWithQuantityProductModels($filteredProductModels, $userId);
        $filteredProductModels = $this->addWorkflowStatusInMetadata($filteredProductModels, $userId);

        return $filteredProductModels;
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
        $viewableAssociatedProductIdentifiers = $this->filterViewableProductIdentifiers($productIdentifiers, $userId);

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
        $viewableAssociatedProductModelCodes = $this->filterViewableProductModelCodes($productModelCodes, $userId);

        return array_map(
            function (ConnectorProductModel $productModel) use ($viewableAssociatedProductModelCodes) {
                return $productModel->filterAssociatedProductModelsByProductModelCodes(
                    $viewableAssociatedProductModelCodes
                );
            },
            $productModels
        );
    }

    private function filterNotGrantedAssociatedWithQuantityProducts(array $productModels, int $userId): array
    {
        $productIdentifiers = array_map(function (ConnectorProductModel $productModel) {
            return $productModel->associatedWithQuantityProductIdentifiers();
        }, $productModels);

        $productIdentifiers = !empty($productIdentifiers) ? array_unique(array_merge(...$productIdentifiers)) : [];
        $viewableAssociatedProductIdentifiers = $this->filterViewableProductIdentifiers($productIdentifiers, $userId);

        return array_map(function (ConnectorProductModel $productModel) use ($viewableAssociatedProductIdentifiers) {
            return $productModel->filterAssociatedWithQuantityProductsByProductIdentifiers(
                $viewableAssociatedProductIdentifiers
            );
        }, $productModels);
    }

    private function filterNotGrantedAssociatedWithQuantityProductModels(array $productModels, int $userId): array
    {
        $productModelCodes = array_map(function (ConnectorProductModel $productModel) {
            return $productModel->associatedWithQuantityProductModelCodes();
        }, $productModels);

        $productModelCodes = !empty($productModelCodes) ? array_unique(array_merge(...$productModelCodes)) : [];
        $viewableAssociatedProductModelCodes = $this->filterViewableProductModelCodes($productModelCodes, $userId);

        return array_map(function (ConnectorProductModel $productModel) use ($viewableAssociatedProductModelCodes) {
            return $productModel->filterAssociatedWithQuantityProductModelsByProductModelCodes($viewableAssociatedProductModelCodes);
        }, $productModels);
    }

    private function addWorkflowStatusInMetadata(array $productModels, int $userId): array
    {
        $productModelCodes = array_map(
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
                if (isset($workflowStatuses[$productModel->code()])) {
                    return $productModel->addMetadata('workflow_status', $workflowStatuses[$productModel->code()]);
                }

                return $productModel;
            },
            $productModels
        );
    }

    private function filterViewableProductIdentifiers(array $productIdentifiers, int $userId)
    {
        $productRights = $this->fetchUserRightsOnProduct->fetchByIdentifiers($productIdentifiers, $userId);
        $viewableAssociatedProducts = array_filter($productRights, function (UserRightsOnProduct $productRight) {
            return $productRight->isProductViewable();
        });

        return array_map(function (UserRightsOnProduct $productRight) {
            return $productRight->productIdentifier();
        }, $viewableAssociatedProducts);
    }

    private function filterViewableProductModelCodes(array $productModelCodes, int $userId)
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
