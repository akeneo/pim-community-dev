<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Attribute\Event;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Symfony\Component\EventDispatcher\Event;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class AttributeDeletedEvent extends Event
{
    /** @var ReferenceEntityIdentifier */
    public $referenceEntityIdentifier;

    /** @var AttributeIdentifier */
    public $attributeIdentifier;

    public function __construct(ReferenceEntityIdentifier $referenceEntityIdentifier, AttributeIdentifier $attributeIdentifier)
    {
        $this->referenceEntityIdentifier = $referenceEntityIdentifier;
        $this->attributeIdentifier = $attributeIdentifier;
    }
}
