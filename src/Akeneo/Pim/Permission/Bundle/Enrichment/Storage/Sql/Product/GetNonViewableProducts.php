<?php

declare(strict_types=1);

namespace Akeneo\Pim\Permission\Bundle\Enrichment\Storage\Sql\Product;

use Akeneo\Pim\Enrichment\Product\Domain\Query\GetNonViewableProducts as GetNonViewableProductsInterface;
use Akeneo\Pim\Permission\Component\Query\ProductCategoryAccessQueryInterface;
use Akeneo\UserManagement\Component\Repository\UserRepositoryInterface;

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class GetNonViewableProducts implements GetNonViewableProductsInterface
{
    public function __construct(
        private ProductCategoryAccessQueryInterface $productCategoryAccessQuery,
        private UserRepositoryInterface $userRepository
    ) {
    }

    public function fromProductIdentifiers(array $productIdentifiers, int $userId)
    {
        if (empty($productIdentifiers)) {
            return [];
        }

        $viewableProducts = $this->productCategoryAccessQuery->getGrantedProductIdentifiers(
            $productIdentifiers,
            $this->userRepository->find($userId)
        );

        return \array_values(\array_diff($productIdentifiers, $viewableProducts));
    }
}
