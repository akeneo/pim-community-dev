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
use Akeneo\AssetManager\Domain\Query\Attribute\ValueKey;
use Akeneo\AssetManager\Domain\Repository\AttributeRepositoryInterface;
use Akeneo\AssetManager\Infrastructure\Transformation\GetOutdatedVariationSource;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class GetOutdatedVariationSourceSpec extends ObjectBehavior
{
    function let(
        AttributeRepositoryInterface $attributeRepository,
        ValidatorInterface $validator,
        MediaFileAttribute $mainImage,
        MediaFileAttribute $targetImage
    ) {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('packshot');
        $attributeRepository->getByCodeAndAssetFamilyIdentifier(
            AttributeCode::fromString('main_image'),
            $assetFamilyIdentifier
        )->willReturn($mainImage);

        $attributeRepository->getByCodeAndAssetFamilyIdentifier(
            AttributeCode::fromString('target_image'),
            $assetFamilyIdentifier
        )->willReturn($targetImage);

        $validator->validate(
            ['normalized_transformation_without_errors'],
            Argument::type(\Akeneo\AssetManager\Infrastructure\Validation\AssetFamily\Transformation::class)
        )->willReturn(new ConstraintViolationList());
        $this->beConstructedWith($attributeRepository, $validator);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(GetOutdatedVariationSource::class);
    }

    function it_throws_an_exception_if_the_transformation_is_not_valid(
        ValidatorInterface $validator,
        Transformation $transformation,
        Asset $asset,
        ConstraintViolationInterface $violation
    ) {
        $transformation->normalize()->willReturn(['normalized_transformation_with_errors']);
        $asset->getAssetFamilyIdentifier()->willReturn(AssetFamilyIdentifier::fromString('packshot'));
        $violation->getMessage()->willReturn('validation error message');
        $validator->validate(
            ['normalized_transformation_with_errors'],
            Argument::type(\Akeneo\AssetManager\Infrastructure\Validation\AssetFamily\Transformation::class)
        )->willReturn(new ConstraintViolationList([$violation->getWrappedObject()]));

        $this->shouldThrow(new NonApplicableTransformationException('validation error message'))->during(
            'forAssetAndTransformation',
            [$asset, $transformation]
        );
    }

    function it_throws_an_exception_if_the_source_file_is_empty(
        Asset $asset,
        Transformation $transformation,
        MediaFileAttribute $mainImage
    ) {
        $transformation->normalize()->willReturn(['normalized_transformation_without_errors']);
        $asset->getAssetFamilyIdentifier()->willReturn(AssetFamilyIdentifier::fromString('packshot'));

        $transformation->getSource()->willReturn(
            Source::createFromNormalized([
                'attribute' => 'main_image',
                'channel' => null,
                'locale' => null,
            ])
        );
        $mainImageIdentifier = AttributeIdentifier::fromString('packshot-main_image-123456');
        $mainImage->getIdentifier()->willReturn($mainImageIdentifier);

        $asset->findValue(ValueKey::createFromNormalized('packshot-main_image-123456'))->willReturn(null);

        $this->shouldThrow(
            new NonApplicableTransformationException('The source file for attribute "main_image" is missing')
        )->during(
            'forAssetAndTransformation',
            [$asset, $transformation]
        );
    }

    function it_returns_null_if_the_target_file_is_newer_than_the_source_file_and_the_transformation(
        Asset $asset,
        Transformation $transformation,
        MediaFileAttribute $mainImage,
        FileData $sourceFile,
        MediaFileAttribute $targetImage,
        FileData $targetFile
    ) {
        $transformation->normalize()->willReturn(['normalized_transformation_without_errors']);
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
        $mainImageIdentifier = AttributeIdentifier::fromString('packshot-main_image-123456');
        $mainImage->getIdentifier()->willReturn($mainImageIdentifier);
        $asset->findValue(ValueKey::createFromNormalized('packshot-main_image-123456'))->willReturn(Value::create(
            $mainImageIdentifier,
            ChannelReference::noReference(),
            LocaleReference::noReference(),
            $sourceFile->getWrappedObject()
        ));

        $transformation->getTarget()->willReturn(
            Target::createFromNormalized(
                [
                    'attribute' => 'target_image',
                    'channel' => null,
                    'locale' => null,
                ]
            )
        );
        $targetImageIdentifier = AttributeIdentifier::fromString('packshot-target_image-789012');
        $targetImage->getIdentifier()->willReturn($targetImageIdentifier);
        $asset->findValue(ValueKey::createFromNormalized('packshot-target_image-789012'))->willReturn(
            Value::create(
                $targetImageIdentifier,
                ChannelReference::noReference(),
                LocaleReference::noReference(),
                $targetFile->getWrappedObject()
            )
        );

        $sourceFile->getUpdatedAt()->willReturn(
            \DateTimeImmutable::createFromFormat(\DateTimeInterface::ISO8601, '2019-11-30T00:00:00+0000')
        );
        $targetFile->getUpdatedAt()->willReturn(
            \DateTimeImmutable::createFromFormat(\DateTimeInterface::ISO8601, '2019-12-05T00:00:00+0000')
        );
        $transformation->getUpdatedAt()->willReturn(
            \DateTimeImmutable::createFromFormat(\DateTimeInterface::ISO8601, '2019-11-15T00:00:00+0000')
        );

        $this->forAssetAndTransformation($asset, $transformation)->shouldReturn(null);
    }

    function it_returns_the_source_file_if_target_file_is_older_than_the_source_file(
        Asset $asset,
        Transformation $transformation,
        MediaFileAttribute $mainImage,
        FileData $sourceFile,
        MediaFileAttribute $targetImage,
        FileData $targetFile
    ) {
        $transformation->normalize()->willReturn(['normalized_transformation_without_errors']);
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
        $mainImageIdentifier = AttributeIdentifier::fromString('packshot-main_image-123456');
        $mainImage->getIdentifier()->willReturn($mainImageIdentifier);
        $asset->findValue(ValueKey::createFromNormalized('packshot-main_image-123456'))->willReturn(
            Value::create(
                $mainImageIdentifier,
                ChannelReference::noReference(),
                LocaleReference::noReference(),
                $sourceFile->getWrappedObject()
            )
        );

        $transformation->getTarget()->willReturn(
            Target::createFromNormalized(
                [
                    'attribute' => 'target_image',
                    'channel' => null,
                    'locale' => null,
                ]
            )
        );
        $targetImageIdentifier = AttributeIdentifier::fromString('packshot-target_image-789012');
        $targetImage->getIdentifier()->willReturn($targetImageIdentifier);
        $asset->findValue(ValueKey::createFromNormalized('packshot-target_image-789012'))->willReturn(
            Value::create(
                $targetImageIdentifier,
                ChannelReference::noReference(),
                LocaleReference::noReference(),
                $targetFile->getWrappedObject()
            )
        );

        $sourceFile->getUpdatedAt()->willReturn(
            \DateTimeImmutable::createFromFormat(\DateTimeInterface::ISO8601, '2019-11-30T00:00:00+0000')
        );
        $targetFile->getUpdatedAt()->willReturn(
            \DateTimeImmutable::createFromFormat(\DateTimeInterface::ISO8601, '2019-11-28T00:00:00+0000')
        );

        $this->forAssetAndTransformation($asset, $transformation)->shouldReturn($sourceFile);
    }

    function it_returns_the_source_file_if_target_file_is_older_than_transformation(
        Asset $asset,
        Transformation $transformation,
        MediaFileAttribute $mainImage,
        FileData $sourceFile,
        MediaFileAttribute $targetImage,
        FileData $targetFile
    ) {
        $transformation->normalize()->willReturn(['normalized_transformation_without_errors']);
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
        $mainImageIdentifier = AttributeIdentifier::fromString('packshot-main_image-123456');
        $mainImage->getIdentifier()->willReturn($mainImageIdentifier);
        $asset->findValue(ValueKey::createFromNormalized('packshot-main_image-123456'))->willReturn(
            Value::create(
                $mainImageIdentifier,
                ChannelReference::noReference(),
                LocaleReference::noReference(),
                $sourceFile->getWrappedObject()
            )
        );

        $transformation->getTarget()->willReturn(
            Target::createFromNormalized(
                [
                    'attribute' => 'target_image',
                    'channel' => null,
                    'locale' => null,
                ]
            )
        );
        $targetImageIdentifier = AttributeIdentifier::fromString('packshot-target_image-789012');
        $targetImage->getIdentifier()->willReturn($targetImageIdentifier);
        $asset->findValue(ValueKey::createFromNormalized('packshot-target_image-789012'))->willReturn(
            Value::create(
                $targetImageIdentifier,
                ChannelReference::noReference(),
                LocaleReference::noReference(),
                $targetFile->getWrappedObject()
            )
        );

        $sourceFile->getUpdatedAt()->willReturn(
            \DateTimeImmutable::createFromFormat(\DateTimeInterface::ISO8601, '2019-11-30T00:00:00+0000')
        );
        $targetFile->getUpdatedAt()->willReturn(
            \DateTimeImmutable::createFromFormat(\DateTimeInterface::ISO8601, '2019-12-02T00:00:00+0000')
        );
        $transformation->getUpdatedAt()->willReturn(
            \DateTimeImmutable::createFromFormat(\DateTimeInterface::ISO8601, '2019-12-05T00:00:00+0000')
        );

        $this->forAssetAndTransformation($asset, $transformation)->shouldReturn($sourceFile);
    }

    function it_returns_the_source_file_if_the_target_is_missing(
        Asset $asset,
        Transformation $transformation,
        MediaFileAttribute $mainImage,
        FileData $sourceFile,
        MediaFileAttribute $targetImage
    ) {
        $transformation->normalize()->willReturn(['normalized_transformation_without_errors']);
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
        $mainImageIdentifier = AttributeIdentifier::fromString('packshot-main_image-123456');
        $mainImage->getIdentifier()->willReturn($mainImageIdentifier);
        $asset->findValue(ValueKey::createFromNormalized('packshot-main_image-123456'))->willReturn(
            Value::create(
                $mainImageIdentifier,
                ChannelReference::noReference(),
                LocaleReference::noReference(),
                $sourceFile->getWrappedObject()
            )
        );

        $transformation->getTarget()->willReturn(
            Target::createFromNormalized(
                [
                    'attribute' => 'target_image',
                    'channel' => null,
                    'locale' => null,
                ]
            )
        );
        $targetImageIdentifier = AttributeIdentifier::fromString('packshot-target_image-789012');
        $targetImage->getIdentifier()->willReturn($targetImageIdentifier);
        $asset->findValue(ValueKey::createFromNormalized('packshot-target_image-789012'))->willReturn(null);

        $this->forAssetAndTransformation($asset, $transformation)->shouldReturn($sourceFile);
    }
}
