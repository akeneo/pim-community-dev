<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Domain\Event;

use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @api
 */
class RecordUpdatedEvent extends Event
{
    public function __construct(private RecordIdentifier $recordIdentifier, private RecordCode $recordCode, private ReferenceEntityIdentifier $referenceEntityIdentifier)
    {
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
