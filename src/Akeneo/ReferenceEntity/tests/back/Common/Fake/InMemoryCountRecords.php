<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Common\Fake;

use Akeneo\ReferenceEntity\Domain\Model\Record\Record;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Record\CountRecordsInterface;

class InMemoryCountRecords implements CountRecordsInterface
{
    /** @var InMemoryFindRecordIdentifiersForQuery */
    private $findRecordIdentifiersForQuery;

    public function __construct(InMemoryFindRecordIdentifiersForQuery $findRecordIdentifiersForQuery)
    {
        $this->findRecordIdentifiersForQuery = $findRecordIdentifiersForQuery;
    }

    public function forReferenceEntity(ReferenceEntityIdentifier $identifierToMatch): int
    {
        return array_reduce(
            $this->findRecordIdentifiersForQuery->getRecords(),
            function (int $numberOfRecords, Record $record) use ($identifierToMatch) {
                if ($record->getReferenceEntityIdentifier()->equals($identifierToMatch)) {
                    $numberOfRecords++;
                }

                return $numberOfRecords;
            },
            0
        );
    }
}
