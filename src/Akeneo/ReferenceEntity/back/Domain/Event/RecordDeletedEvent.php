<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Domain\Event;

use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Symfony\Component\EventDispatcher\Event;

/**
 * Event triggered when a record is deleted from DB
 *
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @internal
 */
class RecordDeletedEvent extends Event
{
    /** @var RecordCode */
    private $recordCode;

    /** @var ReferenceEntityIdentifier */
    private $referenceEntityIdentifier;

    public function __construct(RecordCode $recordCode, ReferenceEntityIdentifier $referenceEntityIdentifier)
    {
        $this->recordCode = $recordCode;
        $this->referenceEntityIdentifier = $referenceEntityIdentifier;
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
