<?php

namespace Akeneo\ReferenceEntity\Domain\Query\Attribute;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\OptionAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\OptionCollectionAttribute;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Repository\AttributeRepositoryInterface;

class AttributeSupportsOptions
{
    public function __construct(private GetAttributeIdentifierInterface $getAttributeIdentifier, private AttributeRepositoryInterface $attributeRepository)
    {
    }

    public function supports(ReferenceEntityIdentifier $referenceEntityIdentifier, AttributeCode $attributeCode): bool
    {
        $identifier = $this->getAttributeIdentifier->withReferenceEntityAndCode($referenceEntityIdentifier, $attributeCode);
        $attribute = $this->attributeRepository->getByIdentifier($identifier);

        return ($attribute instanceof OptionCollectionAttribute || $attribute instanceof OptionAttribute);
    }
}
