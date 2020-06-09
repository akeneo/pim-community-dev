<?php

namespace spec\Akeneo\AssetManager\Domain\Query\AssetFamily\Transformation;

use Akeneo\AssetManager\Domain\Model\Asset\Value\ChannelReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\LocaleReference;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamily;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation\ColorspaceOperation;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\OperationCollection;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Source;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Target;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Transformation;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\TransformationLabel;
use Akeneo\AssetManager\Domain\Model\AssetFamily\TransformationCollection;
use Akeneo\AssetManager\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaFileAttribute;
use Akeneo\AssetManager\Domain\Model\ChannelIdentifier;
use Akeneo\AssetManager\Domain\Model\LocaleIdentifier;
use Akeneo\AssetManager\Domain\Query\AssetFamily\Transformation\CheckIfTransformationTarget;
use Akeneo\AssetManager\Domain\Query\AssetFamily\Transformation\GetTransformations;
use Akeneo\AssetManager\Domain\Query\AssetFamily\Transformation\GetTransformationsSource;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyNotFoundException;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyRepositoryInterface;
use PhpSpec\ObjectBehavior;

class GetTransformationsSourceSpec extends ObjectBehavior
{
    function let(GetTransformations $getTransformations)
    {
        $this->beConstructedWith($getTransformations);
        $this->shouldHaveType(GetTransformationsSource::class);
    }

    function it_returns_empty_array_if_there_are_no_transformation(
        GetTransformations $getTransformations,
        AbstractAttribute $attribute
    ) {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('packshot');
        $channelReference = ChannelReference::noReference();
        $localeReference = LocaleReference::noReference();
        $transformationCollection = TransformationCollection::create([]);

        $attribute->getAssetFamilyIdentifier()->willReturn($assetFamilyIdentifier);
        $getTransformations->fromAssetFamilyIdentifier($assetFamilyIdentifier)->willReturn($transformationCollection);

        $this->forAttribute($attribute, $channelReference, $localeReference)->shouldReturn([]);
    }

    function it_returns_empty_array_if_no_transformation_was_found(
        GetTransformations $getTransformations,
        AbstractAttribute $attribute
    ) {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('packshot');
        $channelReference = ChannelReference::noReference();
        $localeReference = LocaleReference::noReference();

        $getTransformations->fromAssetFamilyIdentifier($assetFamilyIdentifier)->willThrow(AssetFamilyNotFoundException::class);
        $attribute->getAssetFamilyIdentifier()->willReturn($assetFamilyIdentifier);

        $this->forAttribute($attribute, $channelReference, $localeReference)->shouldReturn([]);
    }

    function it_returns_empty_array_if_attribute_is_source_of_none_transformations(
        GetTransformations $getTransformations,
        MediaFileAttribute $attribute,
        MediaFileAttribute $target
    ) {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('packshot');
        $attribute->hasValuePerChannel()->willReturn(true);
        $attribute->hasValuePerLocale()->willReturn(true);
        $attribute->getCode()->willReturn(AttributeCode::fromString('attribute'));

        $target->hasValuePerChannel()->willReturn(false);
        $target->hasValuePerLocale()->willReturn(false);
        $target->getCode()->willReturn(AttributeCode::fromString('target'));

        $transformations = TransformationCollection::create([
            Transformation::create(
                TransformationLabel::fromString('black and white'),
                Source::create(
                    $attribute->getWrappedObject(),
                    ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode('e-commerce')),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('fr_FR'))
                ),
                Target::create(
                    $target->getWrappedObject(),
                    ChannelReference::noReference(),
                    LocaleReference::noReference()
                ),
                OperationCollection::create([
                    ColorspaceOperation::create(['colorspace' => 'grey'])
                ]),
                'bw_',
                null,
                new \DateTimeImmutable()
            ),
        ]);

        $attribute->getAssetFamilyIdentifier()->willReturn($assetFamilyIdentifier);
        $getTransformations->fromAssetFamilyIdentifier($assetFamilyIdentifier)->willReturn($transformations);

        $this->forAttribute(
            $attribute,
            ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode('mobile')),
            LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US'))
        )->shouldReturn([]);
    }

    function it_returns_transformation_sourced_by_attribute(
        GetTransformations $getTransformations,
        MediaFileAttribute $anotherAttributeSource,
        MediaFileAttribute $attribute,
        MediaFileAttribute $target
    ) {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('packshot');
        $attribute->hasValuePerChannel()->willReturn(true);
        $attribute->hasValuePerLocale()->willReturn(true);
        $attribute->getCode()->willReturn(AttributeCode::fromString('attribute'));

        $anotherAttributeSource->hasValuePerChannel()->willReturn(false);
        $anotherAttributeSource->hasValuePerLocale()->willReturn(false);
        $anotherAttributeSource->getCode()->willReturn(AttributeCode::fromString('another_attribute'));

        $target->hasValuePerChannel()->willReturn(true);
        $target->hasValuePerLocale()->willReturn(false);
        $target->getCode()->willReturn(AttributeCode::fromString('target'));

        $transformations = [
            'transformation_attribute_has_source' => Transformation::create(
                TransformationLabel::fromString('black and white'),
                Source::create(
                    $attribute->getWrappedObject(),
                    ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode('mobile')),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US'))
                ),
                Target::create(
                    $target->getWrappedObject(),
                    ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode('mobile')),
                    LocaleReference::noReference()
                ),
                OperationCollection::create([
                    ColorspaceOperation::create(['colorspace' => 'grey'])
                ]),
                'bw_',
                null,
                new \DateTimeImmutable()
            ),
            'another_transformation' => Transformation::create(
                TransformationLabel::fromString('black and white'),
                Source::create(
                    $anotherAttributeSource->getWrappedObject(),
                    ChannelReference::noReference(),
                    LocaleReference::noReference()
                ),
                Target::create(
                    $target->getWrappedObject(),
                    ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode('e-commerce')),
                    LocaleReference::noReference()
                ),
                OperationCollection::create([
                    ColorspaceOperation::create(['colorspace' => 'grey'])
                ]),
                'bw_',
                null,
                new \DateTimeImmutable()
            )
        ];

        $attribute->getAssetFamilyIdentifier()->willReturn($assetFamilyIdentifier);
        $getTransformations->fromAssetFamilyIdentifier($assetFamilyIdentifier)->willReturn(TransformationCollection::create($transformations));

        $this->forAttribute(
            $attribute,
            ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode('mobile')),
            LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US'))
        )->shouldReturn([$transformations['transformation_attribute_has_source']]);
    }
}
