<?php
declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Domain\Query\Record;

use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;

/**
 * Query function that finds SearchRecordItem read models.
 */
interface FindSearchableRecordsInterface
{
    public function byRecordIdentifier(RecordIdentifier $recordIdentifier): ?SearchableRecordItem;

    public function byReferenceEntityIdentifier(ReferenceEntityIdentifier $referenceEntityIdentifier): \Iterator;
}
