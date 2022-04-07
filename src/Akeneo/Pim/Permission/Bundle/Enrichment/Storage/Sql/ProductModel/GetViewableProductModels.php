<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Permission\Bundle\Enrichment\Storage\Sql\ProductModel;

use Akeneo\Pim\Enrichment\Product\Domain\Query\GetViewableProductModels as GetViewableProductModelsInterface;
use Akeneo\Pim\Permission\Component\Authorization\Model\UserRightsOnProductModel;

final class GetViewableProductModels implements GetViewableProductModelsInterface
{
    public function __construct(
        private FetchUserRightsOnProductModel $fetchUserRightsOnProductModel
    ) {
    }

    public function fromProductModelCodes(array $productModelCodes, int $userId): array
    {
        if (empty($productModelCodes)) {
            return [];
        }

        $productModelRights = $this->fetchUserRightsOnProductModel->fetchByIdentifiers($productModelCodes, $userId);
        $viewableAssociatedProducts = array_filter($productModelRights, function (UserRightsOnProductModel $productModelRight) {
            return $productModelRight->isProductModelViewable();
        });

        return array_map(function (UserRightsOnProductModel $productModelRight) {
            return $productModelRight->productModelCode();
        }, $viewableAssociatedProducts);
    }
}
