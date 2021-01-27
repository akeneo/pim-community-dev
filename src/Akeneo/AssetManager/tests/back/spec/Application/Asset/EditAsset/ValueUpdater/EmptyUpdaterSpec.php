<?php

declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Application\Asset\EditAsset\ValueUpdater;

use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditTextValueCommand;
use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EmptyValueCommand;
use Akeneo\AssetManager\Application\Asset\EditAsset\ValueUpdater\EmptyUpdater;
use Akeneo\AssetManager\Domain\Model\Asset\Asset;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ChannelReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\EmptyData;
use Akeneo\AssetManager\Domain\Model\Asset\Value\LocaleReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\Value;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Target;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Transformation;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsReadOnly;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeMaxLength;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOrder;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeRegularExpression;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValidationRule;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaFileAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\TextAttribute;
use Akeneo\AssetManager\Domain\Model\ChannelIdentifier;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Query\AssetFamily\Transformation\GetTransformationsSource;
use Akeneo\AssetManager\Domain\Repository\AttributeRepositoryInterface;
use PhpSpec\ObjectBehavior;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class EmptyUpdaterSpec extends ObjectBehavior
{
    function let(GetTransformationsSource $getTransformationsSource, AttributeRepositoryInterface $attributeRepository)
    {
        $this->beConstructedWith($getTransformationsSource, $attributeRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(EmptyUpdater::class);
    }

    function it_only_supports_empty_value_command(
        EmptyValueCommand $emptyValueCommand,
        EditTextValueCommand $editTextValueCommand
    ) {
        $this->supports($emptyValueCommand)->shouldReturn(true);
        $this->supports($editTextValueCommand)->shouldReturn(false);
    }

    function it_empty_value_of_a_asset(Asset $asset, GetTransformationsSource $getTransformationsSource)
    {
        $textAttribute = $this->getAttribute();
        $editEmptyValueCommand = new EmptyValueCommand($textAttribute, 'ecommerce', 'fr_FR');

        $value = Value::create(
            $editEmptyValueCommand->attribute->getIdentifier(),
            ChannelReference::createFromNormalized($editEmptyValueCommand->channel),
            LocaleReference::createFromNormalized($editEmptyValueCommand->locale),
            EmptyData::create()
        );

        $getTransformationsSource->forAttribute(
            $textAttribute,
            ChannelReference::createFromNormalized('ecommerce'),
            LocaleReference::createFromNormalized('fr_FR')
        )->willReturn([]);

        $this->__invoke($asset, $editEmptyValueCommand);
        $asset->setValue($value)->shouldBeCalled();
    }

    function it_empty_target_values_when_attribute_is_source_of_transformation(
        Asset $asset,
        GetTransformationsSource $getTransformationsSource,
        Transformation $transformation,
        AttributeRepositoryInterface $attributeRepository,
        MediaFileAttribute $targetAttribute
    ) {
        $asset->getAssetFamilyIdentifier()->willReturn(AssetFamilyIdentifier::fromString('packshot'));

        $textAttribute = $this->getAttribute();
        $editEmptyValueCommand = new EmptyValueCommand($textAttribute, 'ecommerce', 'fr_FR');

        $value = Value::create(
            $textAttribute->getIdentifier(),
            ChannelReference::createFromNormalized('ecommerce'),
            LocaleReference::createFromNormalized('fr_FR'),
            EmptyData::create()
        );

        $transformationTargetValue = Value::create(
            AttributeIdentifier::fromString('target_attribute_identifier'),
            ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode('e-commerce')),
            LocaleReference::noReference(),
            EmptyData::create()
        );

        $targetAttribute->getCode()->willReturn(AttributeCode::fromString('target_attribute'));
        $targetAttribute->getIdentifier()->willReturn(AttributeIdentifier::fromString('target_attribute_identifier'));
        $targetAttribute->hasValuePerChannel()->willReturn(true);
        $targetAttribute->hasValuePerLocale()->willReturn(false);
        $transformation->getTarget()->willReturn(Target::create(
            $targetAttribute->getWrappedObject(),
            ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode('e-commerce')),
            LocaleReference::noReference()
        ));

        $getTransformationsSource->forAttribute(
            $textAttribute,
            ChannelReference::createFromNormalized('ecommerce'),
            LocaleReference::createFromNormalized('fr_FR')
        )->willReturn([$transformation])->shouldBeCalled();

        $getTransformationsSource->forAttribute(
            $targetAttribute,
            ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode('e-commerce')),
            LocaleReference::noReference()
        )->willReturn([])->shouldBeCalled();

        $attributeRepository->getByCodeAndAssetFamilyIdentifier('target_attribute', AssetFamilyIdentifier::fromString('packshot'))->willReturn(
            $targetAttribute
        );

        $asset->setValue($value)->shouldBeCalled();
        $asset->setValue($transformationTargetValue)->shouldBeCalled();

        $this->__invoke($asset, $editEmptyValueCommand);
    }

    function it_throws_if_it_does_not_support_the_command(Asset $asset, EditTextValueCommand $editTextValueCommand)
    {
        $this->supports($editTextValueCommand)->shouldReturn(false);
        $this->shouldThrow(\RuntimeException::class)->during('__invoke', [$asset, $editTextValueCommand]);
    }

    private function getAttribute(): TextAttribute
    {
        $textAttribute = TextAttribute::createText(
            AttributeIdentifier::create('designer', 'name', 'test'),
            AssetFamilyIdentifier::fromString('designer'),
            AttributeCode::fromString('name'),
            LabelCollection::fromArray(['fr_FR' => 'Nom', 'en_US' => 'Name']),
            AttributeOrder::fromInteger(0),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(300),
            AttributeValidationRule::none(),
            AttributeRegularExpression::createEmpty()
        );

        return $textAttribute;
    }
}
