<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Domain\Event;

use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Symfony\Component\EventDispatcher\Event;

/**
 * Event triggered when all records belonging to a reference entity are deleted from DB
 *
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @internal
 */
class ReferenceEntityRecordsDeletedEvent extends Event
{
    /** @var ReferenceEntityIdentifier */
    private $referenceEntityIdentifier;

    public function __construct(ReferenceEntityIdentifier $referenceEntityIdentifier)
    {
        $this->referenceEntityIdentifier = $referenceEntityIdentifier;
    }

    public function getReferenceEntityIdentifier(): ReferenceEntityIdentifier
    {
        return $this->referenceEntityIdentifier;
    }
}
