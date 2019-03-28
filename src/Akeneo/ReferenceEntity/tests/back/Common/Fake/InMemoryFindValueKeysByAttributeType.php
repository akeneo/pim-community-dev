<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Common\Fake;

use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\FindValueKeysByAttributeTypeInterface;
use Akeneo\Test\Acceptance\Common\NotImplementedException;

class InMemoryFindValueKeysByAttributeType implements FindValueKeysByAttributeTypeInterface
{
    /**
     * {@inheritdoc}
     */
    public function find(ReferenceEntityIdentifier $referenceEntityIdentifier, array $attributeTypes): array
    {
        throw new NotImplementedException('find');
    }
}
