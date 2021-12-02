<?php

namespace Akeneo\ReferenceEntity\Application\Attribute\EditAttributeOption;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOption\OptionCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\OptionAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\OptionCollectionAttribute;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\GetAttributeIdentifierInterface;
use Akeneo\ReferenceEntity\Domain\Repository\AttributeRepositoryInterface;
use Webmozart\Assert\Assert;

class EditAttributeOptionHandler
{
    public function __construct(
        private GetAttributeIdentifierInterface $getAttributeIdentifier,
        private AttributeRepositoryInterface $attributeRepository
    ) {
    }

    public function __invoke(EditAttributeOptionCommand $command): void
    {
        $attributeIdentifier = $this->getAttributeIdentifier->withReferenceEntityAndCode(
            ReferenceEntityIdentifier::fromString($command->referenceEntityIdentifier),
            AttributeCode::fromString($command->attributeCode)
        );

        $attribute = $this->attributeRepository->getByIdentifier($attributeIdentifier);
        Assert::isInstanceOfAny($attribute, [OptionCollectionAttribute::class, OptionAttribute::class]);
        Assert::true($attribute->hasAttributeOption(OptionCode::fromString($command->optionCode)));

        $option = $attribute->getAttributeOption(OptionCode::fromString($command->optionCode));
        $option->updateLabels(LabelCollection::fromArray($command->labels));

        $this->attributeRepository->update($attribute);
    }
}
