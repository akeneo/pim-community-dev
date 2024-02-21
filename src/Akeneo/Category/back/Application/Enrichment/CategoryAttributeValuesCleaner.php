<?php

declare(strict_types=1);

namespace Akeneo\Category\Application\Enrichment;

use Akeneo\Category\Application\Enrichment\Filter\ByChannelAndLocalesFilter;
use Akeneo\Category\Application\Enrichment\Filter\ByTemplateAttributesUuidsFilter;
use Akeneo\Category\Application\Storage\UpdateCategoryEnrichedValues;
use Akeneo\Category\Domain\Model\Attribute\Attribute;
use Akeneo\Category\Domain\ValueObject\ValueCollection;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryAttributeValuesCleaner
{
    public function __construct(
        private readonly UpdateCategoryEnrichedValues $updateCategoryEnrichedValues,
    ) {
    }

    /**
     * @param array<string, ValueCollection> $valuesByCode
     * @param array<string> $localeCodes
     *
     * @throws \JsonException
     */
    public function cleanByChannelOrLocales(array $valuesByCode, string $channelCode, array $localeCodes): void
    {
        $cleanedEnrichedValues = [];
        foreach ($valuesByCode as $categoryCode => $enrichedValues) {
            $valuesToRemove = ByChannelAndLocalesFilter::getEnrichedValuesToClean(
                $enrichedValues,
                $channelCode,
                $localeCodes,
            );
            if (!empty($valuesToRemove)) {
                foreach ($valuesToRemove as $value) {
                    $enrichedValues->removeValue($value);
                }

                $cleanedEnrichedValues[$categoryCode] = $enrichedValues;
            }
        }

        if (!empty($cleanedEnrichedValues)) {
            $this->updateCategoryEnrichedValues->execute($cleanedEnrichedValues);
        }
    }

    /**
     * @param array<string, ValueCollection> $valuesByCode
     * @param array<Attribute> $templateAttributes
     */
    public function cleanByTemplateAttributesUuid(array $valuesByCode, array $templateAttributes): void
    {
        $cleanedEnrichedValues = [];
        foreach ($valuesByCode as $categoryCode => $enrichedValues) {
            $valuesToRemove = ByTemplateAttributesUuidsFilter::getEnrichedValuesToClean(
                $enrichedValues,
                $templateAttributes,
            );
            if (!empty($valuesToRemove)) {
                foreach ($valuesToRemove as $value) {
                    $enrichedValues->removeValue($value);
                }

                $cleanedEnrichedValues[$categoryCode] = $enrichedValues;
            }

            if (!empty($cleanedEnrichedValues)) {
                $this->updateCategoryEnrichedValues->execute($cleanedEnrichedValues);
            }
        }
    }
}
