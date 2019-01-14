<?php

namespace spec\Akeneo\ReferenceEntity\Application\Attribute\EditAttributeOption;

use Akeneo\ReferenceEntity\Application\Attribute\EditAttributeOption\EditAttributeOptionCommand;
use Akeneo\ReferenceEntity\Application\Attribute\EditAttributeOption\EditAttributeOptionHandler;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOption\AttributeOption;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOption\OptionCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\OptionAttribute;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\GetAttributeIdentifierInterface;
use Akeneo\ReferenceEntity\Domain\Repository\AttributeRepositoryInterface;
use PhpSpec\ObjectBehavior;

class EditAttributeOptionHandlerSpec extends ObjectBehavior
{
    function let(
        GetAttributeIdentifierInterface $getAttributeIdentifier,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->beConstructedWith($getAttributeIdentifier, $attributeRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(EditAttributeOptionHandler::class);
    }

    function it_appends_an_attribute_option($getAttributeIdentifier, $attributeRepository)
    {
        $command = new EditAttributeOptionCommand();
        $command->attributeCode = 'color';
        $command->referenceEntityIdentifier = 'brand';
        $command->optionCode = 'blue';
        $command->labels = [
            'en_US' => 'Blue',
            'de_DE' => 'Blauw',
        ];

        $getAttributeIdentifier->withReferenceEntityAndCode(
            ReferenceEntityIdentifier::fromString('brand'),
            AttributeCode::fromString('color')
        )->willReturn(AttributeIdentifier::fromString('brand'));

        $optionAttribute = OptionAttribute::create(
            AttributeIdentifier::fromString('color'),
            ReferenceEntityIdentifier::fromString('brand'),
            AttributeCode::fromString('red'),
            LabelCollection::fromArray([]),
            AttributeOrder::fromInteger(1),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(true)
        );
        $optionAttribute->setOptions([
            AttributeOption::create(
                OptionCode::fromString('blue'),
                LabelCollection::fromArray(['fr_FR' => 'Bleu clair'])
            )
        ]);

        $expectedOptionAttribute = OptionAttribute::create(
            AttributeIdentifier::fromString('color'),
            ReferenceEntityIdentifier::fromString('brand'),
            AttributeCode::fromString('red'),
            LabelCollection::fromArray([]),
            AttributeOrder::fromInteger(1),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(true)
        );
        // TODO: merge labels instead of replacing them
        $expectedOptionAttribute->setOptions([
            AttributeOption::create(
                OptionCode::fromString('blue'),
                LabelCollection::fromArray(['en_US' => 'Blue', 'de_DE' => 'Blauw'])
            )
        ]);

        $attributeRepository->getByIdentifier(AttributeIdentifier::fromString('brand'))->willReturn($optionAttribute);
        $attributeRepository->update($expectedOptionAttribute)->shouldBeCalled();

        $this->__invoke($command);
    }
}
