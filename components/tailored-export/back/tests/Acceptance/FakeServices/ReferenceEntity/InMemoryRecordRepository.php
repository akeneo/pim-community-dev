<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredExport\Test\Acceptance\FakeServices\ReferenceEntity;

use Akeneo\Platform\TailoredExport\Domain\Query\FindRecordsAttributeValueInterface;
use Akeneo\Platform\TailoredExport\Domain\Query\FindRecordLabelsInterface;

final class InMemoryRecordRepository implements FindRecordLabelsInterface, FindRecordsAttributeValueInterface
{
    private const NULL_VALUE = '<all>';

    private array $recordLabels;
    private array $rawValues;

    public function addRecordLabel(string $attributeCode, string $optionCode, string $locale, string $optionTranslation): void
    {
        $this->recordLabels[$attributeCode][$optionCode][$locale] = $optionTranslation;
    }

    public function addAttributeValue(
        string $referenceEntityCode,
        string $recordCode,
        string $referenceEntityAttributeCode,
        string $value,
        ?string $channel = null,
        ?string $locale = null,
    ): void {
        $this->rawValues[$referenceEntityCode][$recordCode][$referenceEntityAttributeCode][$channel ?? self::NULL_VALUE][$locale ?? self::NULL_VALUE] = $value;
    }

    public function find(
        string $referenceEntityCode,
        array $recordCodes,
        string $referenceEntityAttributeCode,
        ?string $channel = null,
        ?string $locale = null,
    ): array {
        $results = [];
        foreach ($recordCodes as $recordCode) {
            $value = $this->rawValues[$referenceEntityCode][$recordCode][$referenceEntityAttributeCode][$channel ?? self::NULL_VALUE][$locale ?? self::NULL_VALUE] ?? null;

            if (null !== $value) {
                $results[$recordCode] = $value;
            }
        }

        return $results;
    }

    public function byReferenceEntityCodeAndRecordCodes(string $referenceEntityCode, array $recordCodes, string $locale): array
    {
        return array_reduce($recordCodes, function ($carry, $recordCode) use ($referenceEntityCode, $locale) {
            $carry[$recordCode] = $this->recordLabels[$referenceEntityCode][$recordCode][$locale] ?? null;

            return $carry;
        }, []);
    }
}
