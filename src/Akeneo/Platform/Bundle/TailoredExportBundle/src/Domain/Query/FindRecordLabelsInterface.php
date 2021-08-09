<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredExport\Domain\Query;

interface FindRecordLabelsInterface
{
    /**
     * @return array<string, string>
     */
    public function byReferenceEntityCodeAndRecordCodes(
        string $referenceEntityCode,
        array $recordCodes,
        string $locale
    ): array;
}
