<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Domain\Event;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @internal
 */
class AttributeDeletedEvent extends Event
{
    public ReferenceEntityIdentifier $referenceEntityIdentifier;
    public AttributeIdentifier $attributeIdentifier;

    public function __construct(ReferenceEntityIdentifier $referenceEntityIdentifier, AttributeIdentifier $attributeIdentifier)
    {
        $this->referenceEntityIdentifier = $referenceEntityIdentifier;
        $this->attributeIdentifier = $attributeIdentifier;
    }

    public function getReferenceEntityIdentifier(): ReferenceEntityIdentifier
    {
        return $this->referenceEntityIdentifier;
    }

    public function getAttributeIdentifier(): AttributeIdentifier
    {
        return $this->attributeIdentifier;
    }
}
