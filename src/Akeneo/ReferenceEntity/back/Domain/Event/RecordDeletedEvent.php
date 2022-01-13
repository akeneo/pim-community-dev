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
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @api
 */
class RecordDeletedEvent extends Event
{
    public function __construct(
        private RecordIdentifier $recordIdentifier,
        private RecordCode $recordCode,
        private ReferenceEntityIdentifier $referenceEntityIdentifier,
        private bool $isUnitary = true
    ) {
    }

    public function getRecordIdentifier(): RecordIdentifier
    {
        return $this->recordIdentifier;
    }

    public function getRecordCode(): RecordCode
    {
        return $this->recordCode;
    }

    public function getReferenceEntityIdentifier(): ReferenceEntityIdentifier
    {
        return $this->referenceEntityIdentifier;
    }

    public function isUnitary(): bool
    {
        return $this->isUnitary;
    }
}
