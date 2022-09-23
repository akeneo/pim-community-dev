<?php

declare(strict_types=1);

namespace Akeneo\Platform\Syndication\Test\Acceptance\FakeServices\ReferenceEntity;

use Akeneo\Platform\Syndication\Domain\Query\FindRecordLabelsInterface;

final class InMemoryFindRecordLabels implements FindRecordLabelsInterface
{
    private array $recordLabels;

    public function addRecordLabel(string $attributeCode, string $optionCode, string $locale, string $optionTranslation): void
    {
        $this->recordLabels[$attributeCode][$optionCode][$locale] = $optionTranslation;
    }

    public function byReferenceEntityCodeAndRecordCodes(string $referenceEntityCode, array $recordCodes, string $locale): array
    {
        return array_reduce($recordCodes, function ($carry, $recordCode) use ($referenceEntityCode, $locale) {
            $carry[$recordCode] = $this->recordLabels[$referenceEntityCode][$recordCode][$locale] ?? null;

            return $carry;
        }, []);
    }
}
