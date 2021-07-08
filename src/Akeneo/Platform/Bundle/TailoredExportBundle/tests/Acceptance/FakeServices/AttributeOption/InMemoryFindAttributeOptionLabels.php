<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredExport\Test\Acceptance\FakeServices\AttributeOption;

use Akeneo\Platform\TailoredExport\Domain\Query\FindAttributeOptionLabelsInterface;

final class InMemoryFindAttributeOptionLabels implements FindAttributeOptionLabelsInterface
{
    private array $attributeLabels = [];

    public function addAttributeLabel(string $attributeCode, string $optionCode, string $locale, string $optionTranslation)
    {
        $this->attributeLabels[$attributeCode][$optionCode][$locale] = $optionTranslation;
    }

    public function byAttributeCodeAndOptionCodes(string $attributeCode, array $optionCodes, string $locale): array
    {
        return array_reduce($optionCodes, function ($carry, $optionCode) use ($attributeCode, $locale) {
            $carry[$optionCode] = $this->attributeLabels[$attributeCode][$optionCode][$locale] ?? null;

            return $carry;
        }, []);
    }
}
