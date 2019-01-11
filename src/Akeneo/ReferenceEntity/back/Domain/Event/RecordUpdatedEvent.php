<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Domain\Event;

use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Symfony\Component\EventDispatcher\Event;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @internal
 */
class RecordUpdatedEvent extends Event
{
    /** @var RecordIdentifier */
    private $recordIdentifier;

    public function __construct(RecordIdentifier $recordIdentifier)
    {
        $this->recordIdentifier = $recordIdentifier;
    }

    public function getRecordIdentifier(): RecordIdentifier
    {
        return $this->recordIdentifier;
    }
}
