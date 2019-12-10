<?php

namespace spec\Akeneo\AssetManager\Infrastructure\Transformation;

use Akeneo\AssetManager\Application\AssetFamily\Transformation\Exception\NonApplicableTransformationException;
use Akeneo\AssetManager\Domain\Model\Asset\Asset;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ChannelReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\FileData;
use Akeneo\AssetManager\Domain\Model\Asset\Value\LocaleReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\Value;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Source;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Target;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Transformation;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaFileAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\TextAttribute;
use Akeneo\AssetManager\Domain\Query\Attribute\ValueKey;
use Akeneo\AssetManager\Domain\Repository\AttributeRepositoryInterface;
use Akeneo\AssetManager\Infrastructure\Transformation\GetOutdatedVariationSource;
use PhpSpec\ObjectBehavior;

class GetOutdatedVariationSourceSpec extends ObjectBehavior
{
    function let(
        AttributeRepositoryInterface $attributeRepository,
        MediaFileAttribute $mainImage,
        MediaFileAttribute $targetImage
    ) {
        $mainImage->getIdentifier()->willReturn(AttributeIdentifier::fromString('packshot_main_image_123456'));
        $attributeRepository->getByCodeAndAssetFamilyIdentifier(
            AttributeCode::fromString('main_image'),
            AssetFamilyIdentifier::fromString('packshot')
        )->willReturn($mainImage);
        $targetImage->getIdentifier()->willReturn(AttributeIdentifier::fromString('packshot_target_image_123456'));
        $attributeRepository->getByCodeAndAssetFamilyIdentifier(
            AttributeCode::fromString('target_image'),
            AssetFamilyIdentifier::fromString('packshot')
        )->willReturn($targetImage);

        $this->beConstructedWith($attributeRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(GetOutdatedVariationSource::class);
    }

    function it_returns_the_source_file_data_for_an_outdated_target_value(
        Asset $asset,
        Transformation $transformation,
        Value $sourceValue,
        FileData $sourceFileData,
        Value $targetValue,
        FileData $targetFileData
    ) {
        $asset->getAssetFamilyIdentifier()->willReturn(AssetFamilyIdentifier::fromString('packshot'));
        $transformation->getSource()->willReturn(
            Source::createFromNormalized(
                [
                    'attribute' => 'main_image',
                    'channel' => null,
                    'locale' => null,
                ]
            )
        );
        $sourceFileData->getUpdatedAt()->willReturn(
            \DateTimeImmutable::createFromFormat(\DateTimeInterface::ISO8601, '2019-11-30T00:00:21+0000')
        );
        $sourceValue->getData()->willReturn($sourceFileData);
        $asset->findValue(
            ValueKey::create(
                AttributeIdentifier::fromString('packshot_main_image_123456'),
                ChannelReference::noReference(),
                LocaleReference::noReference()
            )
        )->willReturn($sourceValue);

        $transformation->getTarget()->willReturn(
            Target::createFromNormalized(
                [
                    'attribute' => 'target_image',
                    'channel' => null,
                    'locale' => null,
                ]
            )
        );

        $targetFileData->getUpdatedAt()->willReturn(
            \DateTimeImmutable::createFromFormat(\DateTimeInterface::ISO8601, '2019-11-22T15:16:21+0000')
        );
        $targetValue->getData()->willReturn($targetFileData);
        $asset->findValue(
            ValueKey::create(
                AttributeIdentifier::fromString('packshot_target_image_123456'),
                ChannelReference::noReference(),
                LocaleReference::noReference()
            )
        )->willReturn($targetValue);

        $this->forAssetAndTransformation($asset, $transformation)->shouldReturn($sourceFileData);
    }

    function it_returns_null_for_a_non_outdated_target_value(
        Asset $asset,
        Transformation $transformation,
        Value $sourceValue,
        FileData $sourceFileData,
        Value $targetValue,
        FileData $targetFileData
    ) {
        $asset->getAssetFamilyIdentifier()->willReturn(AssetFamilyIdentifier::fromString('packshot'));
        $transformation->getSource()->willReturn(
            Source::createFromNormalized(
                [
                    'attribute' => 'main_image',
                    'channel' => null,
                    'locale' => null,
                ]
            )
        );
        $sourceFileData->getUpdatedAt()->willReturn(
            \DateTimeImmutable::createFromFormat(\DateTimeInterface::ISO8601, '2019-11-01T00:00:21+0000')
        );
        $sourceValue->getData()->willReturn($sourceFileData);
        $asset->findValue(
            ValueKey::create(
                AttributeIdentifier::fromString('packshot_main_image_123456'),
                ChannelReference::noReference(),
                LocaleReference::noReference()
            )
        )->willReturn($sourceValue);

        $transformation->getTarget()->willReturn(
            Target::createFromNormalized(
                [
                    'attribute' => 'target_image',
                    'channel' => null,
                    'locale' => null,
                ]
            )
        );

        $targetFileData->getUpdatedAt()->willReturn(
            \DateTimeImmutable::createFromFormat(\DateTimeInterface::ISO8601, '2019-11-02T15:16:21+0000')
        );
        $targetValue->getData()->willReturn($targetFileData);
        $asset->findValue(
            ValueKey::create(
                AttributeIdentifier::fromString('packshot_target_image_123456'),
                ChannelReference::noReference(),
                LocaleReference::noReference()
            )
        )->willReturn($targetValue);

        $this->forAssetAndTransformation($asset, $transformation)->shouldReturn(null);
    }

    function it_throws_an_exception_if_the_source_attribute_is_not_a_media(
        AttributeRepositoryInterface $attributeRepository,
        Asset $asset,
        Transformation $transformation,
        TextAttribute $mainSource
    ) {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('packshot');
        $asset->getAssetFamilyIdentifier()->willReturn($assetFamilyIdentifier);
        $transformation->getSource()->willReturn(
            Source::createFromNormalized(
                [
                    'attribute' => 'main_source',
                    'channel' => null,
                    'locale' => null,
                ]
            )
        );
        $attributeRepository->getByCodeAndAssetFamilyIdentifier(
            AttributeCode::fromString('main_source'),
            $assetFamilyIdentifier
        )->willReturn($mainSource);

        $this->shouldThrow(
            new NonApplicableTransformationException('source should be a media file')
        )->during('forAssetAndTransformation', [$asset->getWrappedObject(), $transformation->getWrappedObject()]);
    }

    function it_throws_an_exception_if_the_source_value_is_empty(
        Asset $asset,
        Transformation $transformation
    ) {
        $asset->getAssetFamilyIdentifier()->willReturn(AssetFamilyIdentifier::fromString('packshot'));
        $transformation->getSource()->willReturn(
            Source::createFromNormalized(
                [
                    'attribute' => 'main_image',
                    'channel' => null,
                    'locale' => null,
                ]
            )
        );

        $asset->findValue(
            ValueKey::create(
                AttributeIdentifier::fromString('packshot_main_image_123456'),
                ChannelReference::noReference(),
                LocaleReference::noReference()
            )
        )->willReturn(null);

        $this->shouldThrow(
            new NonApplicableTransformationException('source is empty')
        )->during('forAssetAndTransformation', [$asset->getWrappedObject(), $transformation->getWrappedObject()]);
    }

    function it_throws_an_exception_if_the_target_is_no_a_media(
        AttributeRepositoryInterface $attributeRepository,
        Asset $asset,
        Transformation $transformation,
        Value $sourceValue,
        TextAttribute $targetAttribute
    ) {
        $asset->getAssetFamilyIdentifier()->willReturn(AssetFamilyIdentifier::fromString('packshot'));
        $transformation->getSource()->willReturn(
            Source::createFromNormalized(
                [
                    'attribute' => 'main_image',
                    'channel' => null,
                    'locale' => null,
                ]
            )
        );
        $asset->findValue(
            ValueKey::create(
                AttributeIdentifier::fromString('packshot_main_image_123456'),
                ChannelReference::noReference(),
                LocaleReference::noReference()
            )
        )->willReturn($sourceValue);

        $transformation->getTarget()->willReturn(
            Target::createFromNormalized(
                [
                    'attribute' => 'target_attribute',
                    'channel' => null,
                    'locale' => null,
                ]
            )
        );
        $attributeRepository->getByCodeAndAssetFamilyIdentifier(
            AttributeCode::fromString('target_attribute'),
            AssetFamilyIdentifier::fromString('packshot')
        )->willReturn($targetAttribute);

        $this->shouldThrow(
            new NonApplicableTransformationException('target should be a media file')
        )->during('forAssetAndTransformation', [$asset->getWrappedObject(), $transformation->getWrappedObject()]);
    }
}
