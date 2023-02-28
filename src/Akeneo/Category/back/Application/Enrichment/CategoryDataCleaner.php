<?php

declare(strict_types=1);

namespace Akeneo\Category\Application\Enrichment;

use Akeneo\Category\Application\Enrichment\Filter\ByChannelAndLocalesFilter;
use Akeneo\Category\Application\Storage\UpdateCategoryEnrichedValues;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryDataCleaner
{
    public function __construct(
        private readonly UpdateCategoryEnrichedValues $updateCategoryEnrichedValues,
    ) {
    }

    public function cleanByChannelOrLocales(array $valuesByCode, string $channelCode, array $localeCodes): void
    {
        $cleanedEnrichedValues = [];
        foreach ($valuesByCode as $categoryCode => $json) {
            $enrichedValues = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
            $valueKeysToRemove = ByChannelAndLocalesFilter::getEnrichedValueCompositeKeysToClean(
                $enrichedValues,
                $channelCode,
                $localeCodes,
            );
            if (!empty($valueKeysToRemove)) {
                foreach ($valueKeysToRemove as $key) {
                    unset($enrichedValues[$key]);
                }

                $cleanedEnrichedValues[$categoryCode] = json_encode($enrichedValues, JSON_THROW_ON_ERROR);
            }
        }

        if (!empty($cleanedEnrichedValues)) {
            $this->updateCategoryEnrichedValues->execute($cleanedEnrichedValues);
        }
    }
}
