<?php

declare(strict_types=1);

namespace Akeneo\Pim\TableAttribute\Infrastructure\Value\Query;

interface GetExistingRecordCodes
{
    /**
     * @param array<string, string[]> $recordCodes
     * @return array<string, string[]>
     */
    public function fromReferenceEntityIdentifierAndRecordCodes(array $recordCodes): array;
}
