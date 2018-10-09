<?php
declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Infrastructure\Search\Elasticsearch\Record;

use Akeneo\ReferenceEntity\Domain\Model\Record\Record;

interface RecordIndexerInterface
{
    /**
     * Indexes multiple records
     *
     * @param Record[] $records
     */
    public function bulkIndex(array $records);

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
