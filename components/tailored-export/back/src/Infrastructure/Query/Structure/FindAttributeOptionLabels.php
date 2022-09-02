<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredExport\Infrastructure\Query\Structure;

use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeOption\GetExistingAttributeOptionsWithValues;
use Akeneo\Platform\TailoredExport\Domain\Query\FindAttributeOptionLabelsInterface;

class FindAttributeOptionLabels implements FindAttributeOptionLabelsInterface
{
    public function __construct(
        private GetExistingAttributeOptionsWithValues $getExistingAttributeOptionsWithValues,
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function byAttributeCodeAndOptionCodes(string $attributeCode, array $optionCodes, string $locale): array
    {
        $optionKeys = array_map(fn ($optionCode) => sprintf('%s.%s', $attributeCode, $optionCode), $optionCodes);

        $attributeOptionTranslations = $this->getExistingAttributeOptionsWithValues
            ->fromAttributeCodeAndOptionCodes($optionKeys);

        return array_reduce(
            $optionCodes,
            function ($carry, $optionCode) use ($attributeCode, $attributeOptionTranslations, $locale) {
                $optionKey = sprintf('%s.%s', $attributeCode, $optionCode);
                $carry[$optionCode] = $attributeOptionTranslations[$optionKey][$locale] ?? null;

                return $carry;
            },
            [],
        );
    }
}
