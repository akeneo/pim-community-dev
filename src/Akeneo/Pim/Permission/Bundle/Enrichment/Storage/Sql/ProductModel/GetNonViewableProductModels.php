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

use Akeneo\Pim\Enrichment\Product\Domain\Query\GetNonViewableProductModels as GetNonViewableProductModelsInterface;
use Akeneo\Pim\Permission\Component\Query\ProductModelCategoryAccessQueryInterface;
use Akeneo\UserManagement\Component\Repository\UserRepositoryInterface;

class GetNonViewableProductModels implements GetNonViewableProductModelsInterface
{
    public function __construct(
        private ProductModelCategoryAccessQueryInterface $productModelCategoryAccessQuery,
        private UserRepositoryInterface $userRepository
    ) {
    }

    public function fromProductModelCodes(array $productModelCodes, int $userId): array
    {
        if (empty($productModelCodes)) {
            return [];
        }

        $viewableProductModels = $this->productModelCategoryAccessQuery->getGrantedProductModelCodes(
            $productModelCodes,
            $this->userRepository->find($userId)
        );
        return \array_values(\array_diff($productModelCodes, $viewableProductModels));
    }
}
