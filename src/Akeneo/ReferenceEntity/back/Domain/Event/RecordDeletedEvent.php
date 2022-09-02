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
 * @deprecated please use the bulk event Akeneo\ReferenceEntity\Domain\Event\RecordsDeletedEvent instead, even for unitary deletion.
 *             Note: due to scalability issues, this event should not be used anymore, cf. SLA PIM-10391 (on JIRA)
 *
 * @see RecordsDeletedEvent
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
        private ReferenceEntityIdentifier $referenceEntityIdentifier
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
}
