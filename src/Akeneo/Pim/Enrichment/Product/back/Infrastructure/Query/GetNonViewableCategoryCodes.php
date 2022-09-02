<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Infrastructure\Query;

use Akeneo\Pim\Enrichment\Category\API\Query\GetViewableCategories;
use Akeneo\Pim\Enrichment\Product\Domain\Query\GetCategoryCodes;
use Akeneo\Pim\Enrichment\Product\Domain\Query\GetNonViewableCategoryCodes as GetNonViewableCategoryCodesInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetNonViewableCategoryCodes implements GetNonViewableCategoryCodesInterface
{
    public function __construct(
        private GetCategoryCodes $getCategoryCodes,
        private GetViewableCategories $getViewableCategories
    ) {
    }

    public function fromProductUuids(array $productUuids, int $userId): array
    {
        $categoryCodesPerProductUuids = $this->getCategoryCodes->fromProductUuids($productUuids);
        $categoryCodes = [];

        foreach ($categoryCodesPerProductUuids as $categoryCodesForProduct) {
            $categoryCodes = \array_merge($categoryCodes, $categoryCodesForProduct);
        }
        $categoryCodes = \array_values(\array_unique($categoryCodes));
        $viewableCategoryCodes = $this->getViewableCategories->forUserId($categoryCodes, $userId);

        return \array_map(
            static fn (array $categoryCodes): array => \array_values(\array_diff($categoryCodes, $viewableCategoryCodes)),
            $categoryCodesPerProductUuids
        );
    }
}
