<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredExport\Test\Acceptance\FakeServices\ReferenceEntity;

use Akeneo\Platform\TailoredExport\Domain\Query\FindRecordsAttributeValueInterface;

final class InMemoryFindRecordsAttributeValue implements FindRecordsAttributeValueInterface
{
    private const NULL_VALUE = '<all>';
    private array $rawValues;

    public function addAttributeValue(
        string $referenceEntityCode,
        string $recordCode,
        string $referenceEntityAttributeIdentifier,
        string $value,
        ?string $channel = null,
        ?string $locale = null,
    ): void {
        $this->rawValues[$referenceEntityCode][$recordCode][$referenceEntityAttributeIdentifier][$channel ?? self::NULL_VALUE][$locale ?? self::NULL_VALUE] = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function find(
        string $referenceEntityCode,
        array $recordCodes,
        string $referenceEntityAttributeIdentifier,
        ?string $channel = null,
        ?string $locale = null,
    ): array {
        $results = [];
        foreach ($recordCodes as $recordCode) {
            $value = $this->rawValues[$referenceEntityCode][$recordCode][$referenceEntityAttributeIdentifier][$channel ?? self::NULL_VALUE][$locale ?? self::NULL_VALUE] ?? null;

            if (null !== $value) {
                $results[$recordCode] = $value;
            }
        }

        return $results;
    }
}
