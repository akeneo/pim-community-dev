<?php

declare(strict_types=1);

namespace Akeneo\Category\Application\Command;

use Akeneo\Category\Application\Enrichment\CategoryDataCleaner;
use Akeneo\Category\Application\Query\GetEnrichedCategoryValuesOrderedByCategoryCode;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CleanCategoryEnrichedValuesByChannelOrLocaleCommandHandler
{
    private const CATEGORY_BATCH_SIZE = 100;

    public function __construct(
        private readonly GetEnrichedCategoryValuesOrderedByCategoryCode $getEnrichedCategoryValuesOrderedByCategoryCode,
        private readonly CategoryDataCleaner $categoryDataCleaner
    ) {
    }

    public function __invoke(CleanCategoryEnrichedValuesByChannelOrLocaleCommand $command): void
    {
        $offset = 0;

        do {
            $valuesByCode = $this->getEnrichedCategoryValuesOrderedByCategoryCode->byLimitAndOffset(
                self::CATEGORY_BATCH_SIZE,
                $offset
            );
            $offset += self::CATEGORY_BATCH_SIZE;

            $this->categoryDataCleaner->cleanByChannelOrLocales(
                $valuesByCode,
                $command->channelCode,
                $command->localeCodes
            );
        } while (!empty($valuesByCode));
    }
}
