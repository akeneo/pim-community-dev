<?php
declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Infrastructure\Search\Elasticsearch\Record;

use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;

interface RecordIndexerInterface
{
    /**
     * Indexes multiple records
     *
     * @param RecordIdentifier $recordIdentifier
     */
    public function index(RecordIdentifier $recordIdentifier);

    /**
     * Remove all records belonging to a reference entity
     */
    public function removeByReferenceEntityIdentifier(string $referenceEntityIdentifier);

    /**
     * Remove a record from the index
     */
    public function removeRecordByReferenceEntityIdentifierAndCode(
        string $referenceEntityIdentifier,
        string $recordCode
    );
}
