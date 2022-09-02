<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Permission\Bundle\Enrichment\Storage\Sql\Product;

use Akeneo\Pim\Enrichment\Product\Domain\Query\GetViewableProducts as GetViewableProductsInterface;
use Akeneo\Pim\Permission\Component\Authorization\Model\UserRightsOnProduct;

final class GetViewableProducts implements GetViewableProductsInterface
{
    public function __construct(
        private FetchUserRightsOnProduct $fetchUserRightsOnProduct,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function fromProductIdentifiers(array $productIdentifiers, int $userId): array
    {
        if (empty($productIdentifiers)) {
            return [];
        }

        $productRights = $this->fetchUserRightsOnProduct->fetchByIdentifiers($productIdentifiers, $userId);
        $viewableAssociatedProducts = array_filter($productRights, function (UserRightsOnProduct $productRight) {
            return $productRight->isProductViewable();
        });

        return array_map(function (UserRightsOnProduct $productRight) {
            return $productRight->productIdentifier();
        }, $viewableAssociatedProducts);
    }
}
