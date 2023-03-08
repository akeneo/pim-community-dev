<?php

declare(strict_types=1);

namespace Akeneo\Category\Application\Command\CleanCategoryEnrichedValuesByChannelOrLocale;

use Akeneo\Category\Application\Enrichment\CategoryDataCleaner;
use Akeneo\Category\Application\Query\GetEnrichedValuesPerCategoryCode;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CleanCategoryEnrichedValuesByChannelOrLocaleCommandHandler
{
    private const CATEGORY_BATCH_SIZE = 100;

    public function __construct(
        private readonly GetEnrichedValuesPerCategoryCode $getEnrichedValuesPerCategoryCode,
        private readonly CategoryDataCleaner              $categoryDataCleaner,
    ) {
    }

    public function __invoke(CleanCategoryEnrichedValuesByChannelOrLocaleCommand $command): void
    {
        foreach ($this->getEnrichedValuesPerCategoryCode->byBatchesOf(self::CATEGORY_BATCH_SIZE) as $valuesByCode) {
            if (count($valuesByCode) !== 0) {
                $this->categoryDataCleaner->cleanByChannelOrLocales(
                    $valuesByCode,
                    $command->channelCode,
                    $command->localeCodes,
                );
            }
        }
    }
}
