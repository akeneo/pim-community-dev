<?php

declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Infrastructure\PublicApi\Enrich;

use Akeneo\AssetManager\Domain\Model\Asset\Asset;
use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ChannelReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\LocaleReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\Value;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ValueDataInterface;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamily;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AttributeAsMainMediaReference;
use Akeneo\AssetManager\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Query\Attribute\ValueKey;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyRepositoryInterface;
use Akeneo\AssetManager\Domain\Repository\AssetRepositoryInterface;
use Akeneo\AssetManager\Domain\Repository\AttributeRepositoryInterface;
use Akeneo\AssetManager\Infrastructure\Persistence\Sql\Asset\Hydrator\AssetItem\ImagePreviewUrlGenerator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class AssetPreviewGeneratorSpec extends ObjectBehavior
{
    function let(
        AssetRepositoryInterface $assetRepository,
        AssetFamilyRepositoryInterface $assetFamilyRepository,
        AttributeRepositoryInterface $attributeRepository,
        ImagePreviewUrlGenerator $imagePreviewUrlGenerator
    ) {
        $this->beConstructedWith(
            $assetRepository,
            $assetFamilyRepository,
            $attributeRepository,
            $imagePreviewUrlGenerator
        );
    }

    public function it_gets_the_url_of_an_asset_with_media_file_attribute(
        AssetRepositoryInterface $assetRepository,
        AssetFamilyRepositoryInterface $assetFamilyRepository,
        AttributeRepositoryInterface $attributeRepository,
        ImagePreviewUrlGenerator $imagePreviewUrlGenerator,
        Asset $asset,
        AssetFamily $assetFamily,
        AttributeAsMainMediaReference $attributeAsMainMediaReference,
        AttributeIdentifier $attributeIdentifier,
        AbstractAttribute $attribute,
        Value $value,
        ValueDataInterface $data
    ) {
        $assetRepository->getByAssetFamilyAndCode(
            AssetFamilyIdentifier::fromString('packshot'),
            AssetCode::fromString('iphone')
        )->willReturn($asset);

        $assetFamilyRepository->getByIdentifier(AssetFamilyIdentifier::fromString('packshot'))
            ->willReturn($assetFamily);

        $assetFamily->getAttributeAsMainMediaReference()->willReturn($attributeAsMainMediaReference);
        $attributeAsMainMediaReference->getIdentifier()->willReturn($attributeIdentifier);
        $attributeIdentifier->stringValue()->willReturn('packshot');
        $attributeIdentifier->normalize()->willReturn('packshot');

        $attributeRepository->getByIdentifier($attributeIdentifier)
            ->willReturn($attribute);

        $attribute->hasValuePerChannel()->willReturn(true);
        $attribute->hasValuePerLocale()->willReturn(true);

        $asset->findValue(ValueKey::create(
            $attributeIdentifier->getWrappedObject(),
            ChannelReference::createFromNormalized('mobile'),
            LocaleReference::noReference()
        ))->willReturn($value);

        $value->getData()->willReturn($data);
        $data->normalize()->willReturn([
            'filePath' => '/a/b/c/d/jambon.png'
        ]);

        $imagePreviewUrlGenerator->generate(
            Argument::that(
                function ($base64EncodedData) {
                    return '/a/b/c/d/jambon.png' === base64_decode((string) $base64EncodedData);
                }
            ),
            'packshot',
            'thumbnail'
        )->willReturn('URL_TO_IMAGE');

        $this->getImageUrl('iphone', 'packshot', 'mobile', null, 'thumbnail')
            ->shouldReturn('URL_TO_IMAGE');
    }

    public function it_gets_the_url_of_an_asset_with_media_link_attribute(
        AssetRepositoryInterface $assetRepository,
        AssetFamilyRepositoryInterface $assetFamilyRepository,
        AttributeRepositoryInterface $attributeRepository,
        ImagePreviewUrlGenerator $imagePreviewUrlGenerator,
        Asset $asset,
        AssetFamily $assetFamily,
        AttributeAsMainMediaReference $attributeAsMainMediaReference,
        AttributeIdentifier $attributeIdentifier,
        AbstractAttribute $attribute,
        Value $value,
        ValueDataInterface $data
    ) {
        $assetRepository->getByAssetFamilyAndCode(
            AssetFamilyIdentifier::fromString('packshot'),
            AssetCode::fromString('iphone')
        )->willReturn($asset);

        $assetFamilyRepository->getByIdentifier(AssetFamilyIdentifier::fromString('packshot'))
            ->willReturn($assetFamily);

        $assetFamily->getAttributeAsMainMediaReference()->willReturn($attributeAsMainMediaReference);
        $attributeAsMainMediaReference->getIdentifier()->willReturn($attributeIdentifier);
        $attributeIdentifier->stringValue()->willReturn('packshot');
        $attributeIdentifier->normalize()->willReturn('packshot');

        $attributeRepository->getByIdentifier($attributeIdentifier)
            ->willReturn($attribute);

        $attribute->hasValuePerChannel()->willReturn(true);
        $attribute->hasValuePerLocale()->willReturn(true);

        $asset->findValue(ValueKey::create(
            $attributeIdentifier->getWrappedObject(),
            ChannelReference::noReference(),
            LocaleReference::noReference()
        ))->willReturn($value);

        $value->getData()->willReturn($data);
        $data->normalize()->willReturn('http://www.example.org/image.png');

        $imagePreviewUrlGenerator->generate(
            Argument::that(
                function ($base64EncodedData) {
                    return 'http://www.example.org/image.png' === base64_decode((string) $base64EncodedData);
                }
            ),
            'packshot',
            'thumbnail'
        )->willReturn('URL_TO_IMAGE');

        $this->getImageUrl('iphone', 'packshot', null, null, 'thumbnail')
            ->shouldReturn('URL_TO_IMAGE');
    }
}
