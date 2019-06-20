<?php
declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Domain\Repository;

use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;

interface RecordIndexerInterface
{
    /**
     * Indexes multiple records
     *
     * @param RecordIdentifier $recordIdentifier
     */
    public function index(RecordIdentifier $recordIdentifier);

    /**
     * Indexes all records belonging to the given reference entity.
     */
    public function indexByReferenceEntity(ReferenceEntityIdentifier $referenceEntityIdentifier): void;

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

    public function refresh();
}
