<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Domain\Event;

use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Event triggered when a record is deleted from DB
 *
 * @author    Adrien Migaire <adrien.migaire@akeneo.com>
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @api
 */
class RecordsDeletedEvent extends Event
{
    /**
     * @param RecordIdentifier[] $recordIdentifiers
     * @param RecordCode[] $recordCodes
     */
    public function __construct(
        private array $recordIdentifiers,
        private array $recordCodes,
        private ReferenceEntityIdentifier $referenceEntityIdentifier
    ) {
    }

    /**
     * @return RecordIdentifier[]
     */
    public function getRecordIdentifiers(): array
    {
        return $this->recordIdentifiers;
    }

    /**
     * @return RecordCode[]
     */
    public function getRecordCodes(): array
    {
        return $this->recordCodes;
    }

    public function getReferenceEntityIdentifier(): ReferenceEntityIdentifier
    {
        return $this->referenceEntityIdentifier;
    }
}
