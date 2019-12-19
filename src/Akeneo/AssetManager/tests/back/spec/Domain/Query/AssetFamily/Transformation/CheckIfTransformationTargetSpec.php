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
use Akeneo\AssetManager\Domain\Query\AssetFamily\Transformation\CheckIfTransformationTarget;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyRepositoryInterface;
use PhpSpec\ObjectBehavior;

class CheckIfTransformationTargetSpec extends ObjectBehavior
{
    function let(AssetFamilyRepositoryInterface $assetFamilyRepository)
    {
        $this->beConstructedWith($assetFamilyRepository);
        $this->shouldHaveType(CheckIfTransformationTarget::class);
    }

    function it_returns_false_if_there_are_no_transformation(
        AssetFamilyRepositoryInterface $assetFamilyRepository,
        AbstractAttribute $attribute,
        AssetFamily $assetFamily
    ) {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('packshot');
        $attribute->getAssetFamilyIdentifier()->willReturn($assetFamilyIdentifier);
        $transformations = TransformationCollection::noTransformation();
        $assetFamilyRepository->getByIdentifier($assetFamilyIdentifier)->willReturn($assetFamily);
        $assetFamily->getTransformationCollection()->willReturn($transformations);

        $this->forAttribute($attribute, null, null)->shouldReturn(false);
    }

    function it_returns_false_if_no_transformation_match(
        AssetFamilyRepositoryInterface $assetFamilyRepository,
        MediaFileAttribute $attribute,
        MediaFileAttribute $target,
        AssetFamily $assetFamily
    ) {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('packshot');

        $attribute->getAssetFamilyIdentifier()->willReturn($assetFamilyIdentifier);
        $attribute->hasValuePerChannel()->willReturn(false);
        $attribute->hasValuePerLocale()->willReturn(false);
        $attribute->getCode()->willReturn(AttributeCode::fromString('attribute'));

        $target->hasValuePerChannel()->willReturn(false);
        $target->hasValuePerLocale()->willReturn(false);
        $target->getCode()->willReturn(AttributeCode::fromString('target'));

        $transformations = TransformationCollection::create([
            Transformation::create(
                TransformationLabel::fromString('black and white'),
                Source::create(
                    $attribute->getWrappedObject(),
                    ChannelReference::noReference(),
                    LocaleReference::noReference()
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
            )
        ]);

        $assetFamilyRepository->getByIdentifier($assetFamilyIdentifier)->willReturn($assetFamily);
        $assetFamily->getTransformationCollection()->willReturn($transformations);

        $this->forAttribute($attribute, null, null)->shouldReturn(false);
    }

    function it_returns_true_if_a_transformation_match(
        AssetFamilyRepositoryInterface $assetFamilyRepository,
        MediaFileAttribute $attribute,
        MediaFileAttribute $source,
        AssetFamily $assetFamily
    ) {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('packshot');
        $attribute->getAssetFamilyIdentifier()->willReturn($assetFamilyIdentifier);

        $attribute->getAssetFamilyIdentifier()->willReturn($assetFamilyIdentifier);
        $attribute->hasValuePerChannel()->willReturn(false);
        $attribute->hasValuePerLocale()->willReturn(false);
        $attribute->getCode()->willReturn(AttributeCode::fromString('attribute'));

        $source->hasValuePerChannel()->willReturn(false);
        $source->hasValuePerLocale()->willReturn(false);
        $source->getCode()->willReturn(AttributeCode::fromString('source'));

        $transformations = TransformationCollection::create([
            Transformation::create(
                TransformationLabel::fromString('black and white'),
                Source::create(
                    $source->getWrappedObject(),
                    ChannelReference::noReference(),
                    LocaleReference::noReference()
                ),
                Target::create(
                    $attribute->getWrappedObject(),
                    ChannelReference::noReference(),
                    LocaleReference::noReference()
                ),
                OperationCollection::create([
                    ColorspaceOperation::create(['colorspace' => 'grey'])
                ]),
                'bw_',
                null,
                new \DateTimeImmutable()
            )
        ]);

        $assetFamilyRepository->getByIdentifier($assetFamilyIdentifier)->willReturn($assetFamily);
        $assetFamily->getTransformationCollection()->willReturn($transformations);

        $this->forAttribute($attribute, null, null)->shouldReturn(true);
    }
}
