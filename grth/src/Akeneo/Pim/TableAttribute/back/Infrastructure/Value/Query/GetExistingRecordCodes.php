<?php

declare(strict_types=1);

namespace Akeneo\Pim\TableAttribute\Infrastructure\Value\Query;

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ReferenceEntityIdentifier;

interface GetExistingRecordCodes
{
    /**
     * @param ReferenceEntityIdentifier $referenceEntityIdentifier
     * @param string[] $recordCodes
     * @return string[]
     */
    public function fromReferenceEntityIdentifierAndRecordCodes(ReferenceEntityIdentifier $referenceEntityIdentifier, array $recordCodes): array;
}
