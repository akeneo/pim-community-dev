<?php

namespace Akeneo\ReferenceEntity\Domain\Query\Attribute;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\OptionAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\OptionCollectionAttribute;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;

class AttributeSupportsOptions
{
    public function __invoke(AttributeCode $attributeCode, ReferenceEntityIdentifier $referenceEntityIdentifier): bool
    {
        $attribute = [];

        return ($attribute instanceof OptionCollectionAttribute || $attribute instanceof OptionAttribute);
    }
}
