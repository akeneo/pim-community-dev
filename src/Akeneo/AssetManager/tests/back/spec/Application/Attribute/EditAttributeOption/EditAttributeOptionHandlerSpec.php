<?php

namespace spec\Akeneo\AssetManager\Application\Attribute\EditAttributeOption;

use Akeneo\AssetManager\Application\Attribute\EditAttributeOption\EditAttributeOptionCommand;
use Akeneo\AssetManager\Application\Attribute\EditAttributeOption\EditAttributeOptionHandler;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOption\AttributeOption;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOption\OptionCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOrder;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\AssetManager\Domain\Model\Attribute\OptionAttribute;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\Attribute\GetAttributeIdentifierInterface;
use Akeneo\AssetManager\Domain\Repository\AttributeRepositoryInterface;
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
        $command = new EditAttributeOptionCommand(
            'brand',
            'color',
            'blue',
            [
                'en_US' => 'Blue',
                'de_DE' => 'Blauw',
            ]
        );

        $getAttributeIdentifier->withAssetFamilyAndCode(
            AssetFamilyIdentifier::fromString('brand'),
            AttributeCode::fromString('color')
        )->willReturn(AttributeIdentifier::fromString('brand'));

        $optionAttribute = OptionAttribute::create(
            AttributeIdentifier::fromString('color'),
            AssetFamilyIdentifier::fromString('brand'),
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
            AssetFamilyIdentifier::fromString('brand'),
            AttributeCode::fromString('red'),
            LabelCollection::fromArray([]),
            AttributeOrder::fromInteger(1),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(true)
        );
        $expectedOptionAttribute->setOptions([
            AttributeOption::create(
                OptionCode::fromString('blue'),
                LabelCollection::fromArray(['fr_FR' => 'Bleu clair', 'en_US' => 'Blue', 'de_DE' => 'Blauw'])
            )
        ]);

        $attributeRepository->getByIdentifier(AttributeIdentifier::fromString('brand'))->willReturn($optionAttribute);
        $attributeRepository->update($expectedOptionAttribute)->shouldBeCalled();

        $this->__invoke($command);
    }
}
