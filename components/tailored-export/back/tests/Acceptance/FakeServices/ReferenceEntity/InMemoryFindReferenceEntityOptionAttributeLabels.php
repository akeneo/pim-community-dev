<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredExport\Test\Acceptance\FakeServices\ReferenceEntity;

use Akeneo\Platform\TailoredExport\Domain\Query\FindReferenceEntityOptionAttributeLabelsInterface;

final class InMemoryFindReferenceEntityOptionAttributeLabels implements FindReferenceEntityOptionAttributeLabelsInterface
{
    private array $rawValues;

    public function addOptionLabel(
        string $attributeIdentifier,
        string $optionCode,
        string $localeCode,
        string $label,
    ): void {
        $this->rawValues[$attributeIdentifier][$optionCode][$localeCode] = $label;
    }

    /**
     * {@inheritdoc}
     */
    public function find(string $attributeIdentifier): array
    {
        return $this->rawValues[$attributeIdentifier] ?? [];
    }
}
