<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Infrastructure\PublicApi\Enrich;

interface FindRecordsLabelTranslationsInterface
{
    public function find(string $referenceEntityCode, array $recordCodes, $locale): array;
}
