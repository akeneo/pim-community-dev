<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Common\Fake;

use Akeneo\ReferenceEntity\Domain\Model\Record\Record;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Record\CountRecordsInterface;

class InMemoryCountRecords implements CountRecordsInterface
{
    public function forReferenceEntity(ReferenceEntityIdentifier $identifierToMatch): int
    {
        return 3;
    }
}
