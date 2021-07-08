<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredExport\Test\Acceptance\FakeServices\ReferenceEntity;

use Akeneo\ReferenceEntity\Infrastructure\PublicApi\Enrich\FindRecordsLabelTranslationsInterface;

final class InMemoryFindRecordsLabelTranslations implements FindRecordsLabelTranslationsInterface
{
    private array $recordLabels;

    public function addRecordLabel(string $attributeCode, string $optionCode, string $locale, string $optionTranslation)
    {
        $this->recordLabels[$attributeCode][$optionCode][$locale] = $optionTranslation;
    }

    public function find(string $referenceEntityCode, array $recordCodes, $locale): array
    {
        return array_reduce($recordCodes, function ($carry, $recordCode) use ($referenceEntityCode, $locale) {
            $carry[$recordCode] = $this->recordLabels[$referenceEntityCode][$recordCode][$locale] ?? null;

            return $carry;
        }, []);
    }
}
