<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Infrastructure\Query;

use Akeneo\Pim\Enrichment\Category\API\Query\GetViewableCategories;
use Akeneo\Pim\Enrichment\Product\Domain\Query\GetCategoryCodes;
use Akeneo\Pim\Enrichment\Product\Domain\Query\GetNonViewableCategoryCodes;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SqlGetNonViewableCategoryCodes implements GetNonViewableCategoryCodes
{
    public function __construct(
        private GetCategoryCodes $getCategoryCodes,
        private GetViewableCategories $getViewableCategories
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function fromProductIdentifiers(array $productIdentifiers, int $userId): array
    {
        $categoryCodesPerProductIdentifier = $this->getCategoryCodes->fromProductIdentifiers($productIdentifiers);
        $categoryCodes = [];
        foreach ($categoryCodesPerProductIdentifier as $categoryCodesForProduct) {
            $categoryCodes = \array_merge($categoryCodes, $categoryCodesForProduct);
        }
        $categoryCodes = \array_values(\array_unique($categoryCodes));

        $viewableCategoryCodes = $this->getViewableCategories->forUserId($categoryCodes, $userId);

        return \array_map(
            static fn (array $categoryCodes): array => \array_values(\array_diff($categoryCodes, $viewableCategoryCodes)),
            $categoryCodesPerProductIdentifier
        );
    }
}
