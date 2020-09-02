<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Enrichment\AssetManager\Bundle\Datagrid\Normalizer;

use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Query\Asset\AssetDetails;
use Akeneo\AssetManager\Domain\Query\Asset\FindAssetDetailsInterface;
use Akeneo\Pim\Enrichment\AssetManager\Component\Query\GetAssetInformationQueryInterface;
use Akeneo\Pim\Enrichment\AssetManager\Component\Value\AssetCollectionValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\ReferenceDataCollectionValue;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;

class AssetCollectionValueNormalizerSpec extends ObjectBehavior
{
    public function let(
        IdentifiableObjectRepositoryInterface $attributeRepository,
        GetAssetInformationQueryInterface $getAssetInformationQuery,
        FindAssetDetailsInterface $findAssetDetailsQuery,
        AssetCollectionValueInterface $assetCollectionValue,
        AssetCode $assetCode
    ) {
        $assetCollectionValue->getAttributeCode()->willReturn('attribute_collection');
        $assetCollectionValue->getData()->willReturn([$assetCode]);
        $assetCollectionValue->getLocaleCode()->willReturn('fr_FR');
        $assetCollectionValue->getScopeCode()->willReturn('mobile');

        $this->beConstructedWith($attributeRepository, $getAssetInformationQuery, $findAssetDetailsQuery);
    }

    function it_normalize_only_datagrid_asset_collection_value(AssetCollectionValueInterface $assetCollectionValue)
    {
        $this->supportsNormalization($assetCollectionValue, 'datagrid')->shouldReturn(true);
    }

    function it_does_not_support_other_format(AssetCollectionValueInterface $assetCollectionValue)
    {
        $this->supportsNormalization($assetCollectionValue, 'standard')->shouldReturn(false);
    }

    function it_does_not_support_other_data(ProductInterface $product)
    {
        $this->supportsNormalization($product, 'datagrid')->shouldReturn(false);
    }

    function it_returns_null_when_value_is_null(AssetCollectionValueInterface $assetCollectionValue)
    {
        $assetCollectionValue->getData()->willReturn(null);

        $this->normalize($assetCollectionValue, null, ['data_locale' => 'fr_FR', 'data_channel' => 'mobile'])->shouldReturn(null);
    }

    function it_returns_null_when_value_is_empty(AssetCollectionValueInterface $assetCollectionValue)
    {
        $assetCollectionValue->getData()->willReturn([]);

        $this->normalize($assetCollectionValue, null, ['data_locale' => 'fr_FR', 'data_channel' => 'mobile'])->shouldReturn(null);
    }

    function it_returns_empty_data_when_attribute_is_not_found(
        IdentifiableObjectRepositoryInterface $attributeRepository,
        AssetCollectionValueInterface $assetCollectionValue,
        AssetCode $assetCode
    ) {
        $assetCollectionValue->getAttributeCode()->willReturn('unknown');

        $attributeRepository->findOneByIdentifier('unknown')->willReturn(null);

        $this->normalize($assetCollectionValue, null, ['data_locale' => 'fr_FR', 'data_channel' => 'mobile'])->shouldReturn([
            'locale' => 'fr_FR',
            'scope'  => 'mobile',
            'data'   => null,
        ]);
    }

    function it_return_empty_data_when_asset_details_is_not_found(
        IdentifiableObjectRepositoryInterface $attributeRepository,
        AssetCollectionValueInterface $assetCollectionValue,
        AssetCode $assetCode,
        FindAssetDetailsInterface $findAssetDetailsQuery,
        AttributeInterface $attribute
    ) {
        $attributeRepository->findOneByIdentifier('attribute_collection')->willReturn($attribute);

        $attribute->getReferenceDataName()->willReturn('atmosphere');
        $findAssetDetailsQuery->find(AssetFamilyIdentifier::fromString('atmosphere'), $assetCode)->willReturn(null);

        $this->normalize($assetCollectionValue, null, ['data_locale' => 'fr_FR', 'data_channel' => 'mobile'])->shouldReturn([
            'locale' => 'fr_FR',
            'scope'  => 'mobile',
            'data'   => null,
        ]);
    }

    function it_return_only_attribute_data_when_no_image_was_found_for_scope_and_locale_to_display_default_image(
        IdentifiableObjectRepositoryInterface $attributeRepository,
        AssetCollectionValueInterface $assetCollectionValue,
        AssetCode $assetCode,
        FindAssetDetailsInterface $findAssetDetailsQuery,
        AttributeInterface $attribute
    ) {
        $attribute->getReferenceDataName()->willReturn('atmosphere')->shouldBeCalled();
        $attributeRepository->findOneByIdentifier('attribute_collection')->willReturn($attribute)->shouldBeCalled();

        $assetDetails = new AssetDetails(
            AssetIdentifier::fromString('atmosphere_index_b4d9d237-a2e5-4ac5-83c1-22dd25f33202'),
            AssetFamilyIdentifier::fromString('atmosphere'),
            AttributeIdentifier::fromString('reference_atmosphere_249bf90c-0176-4895-9eed-486fce0fbbe4'),
            AssetCode::fromString('index'),
            LabelCollection::fromArray([]),
            [],
            [],
            false
        );

        $findAssetDetailsQuery->find(AssetFamilyIdentifier::fromString('atmosphere'), $assetCode)->willReturn($assetDetails)->shouldBeCalled();

        $this->normalize($assetCollectionValue, null, ['data_locale' => 'fr_FR', 'data_channel' => 'mobile'])->shouldReturn([
            'locale' => 'fr_FR',
            'scope'  => 'mobile',
            'data'   => [
                'attribute' => 'reference_atmosphere_249bf90c-0176-4895-9eed-486fce0fbbe4',
            ],
        ]);
    }

    function it_return_scoped_and_localized_image_when_media_is_scoped_and_localized(
        IdentifiableObjectRepositoryInterface $attributeRepository,
        AssetCollectionValueInterface $assetCollectionValue,
        AssetCode $assetCode,
        FindAssetDetailsInterface $findAssetDetailsQuery,
        AttributeInterface $attribute
    ) {
        $attribute->getReferenceDataName()->willReturn('atmosphere')->shouldBeCalled();
        $attributeRepository->findOneByIdentifier('attribute_collection')->willReturn($attribute)->shouldBeCalled();

        $images = $this->getImages();
        $assetDetails = new AssetDetails(
            AssetIdentifier::fromString('atmosphere_index_b4d9d237-a2e5-4ac5-83c1-22dd25f33202'),
            AssetFamilyIdentifier::fromString('atmosphere'),
            AttributeIdentifier::fromString('reference_atmosphere_249bf90c-0176-4895-9eed-486fce0fbbe4'),
            AssetCode::fromString('index'),
            LabelCollection::fromArray([]),
            $images,
            [],
            false
        );

        $findAssetDetailsQuery->find(AssetFamilyIdentifier::fromString('atmosphere'), $assetCode)->willReturn($assetDetails)->shouldBeCalled();

        $this->normalize($assetCollectionValue, null, ['data_locale' => 'fr_FR', 'data_channel' => 'mobile'])->shouldReturn([
            'locale' => 'fr_FR',
            'scope'  => 'mobile',
            'data'   => [
                'data' => $images['image_localized_and_scoped']['data'],
                'attribute' => 'reference_atmosphere_249bf90c-0176-4895-9eed-486fce0fbbe4',
            ],
        ]);
    }

    function it_return_scoped_image_when_media_is_scoped(
        IdentifiableObjectRepositoryInterface $attributeRepository,
        AssetCollectionValueInterface $assetCollectionValue,
        AssetCode $assetCode,
        FindAssetDetailsInterface $findAssetDetailsQuery,
        AttributeInterface $attribute
    ) {
        $attribute->getReferenceDataName()->willReturn('atmosphere')->shouldBeCalled();
        $attributeRepository->findOneByIdentifier('attribute_collection')->willReturn($attribute)->shouldBeCalled();

        $images = $this->getImages();
        $assetDetails = new AssetDetails(
            AssetIdentifier::fromString('atmosphere_index_b4d9d237-a2e5-4ac5-83c1-22dd25f33202'),
            AssetFamilyIdentifier::fromString('atmosphere'),
            AttributeIdentifier::fromString('reference_atmosphere_249bf90c-0176-4895-9eed-486fce0fbbe4'),
            AssetCode::fromString('index'),
            LabelCollection::fromArray([]),
            $images,
            [],
            false
        );

        $findAssetDetailsQuery->find(AssetFamilyIdentifier::fromString('atmosphere'), $assetCode)->willReturn($assetDetails)->shouldBeCalled();

        $this->normalize($assetCollectionValue, null, ['data_locale' => 'fr_FR', 'data_channel' => 'e-commerce'])->shouldReturn([
            'locale' => 'fr_FR',
            'scope'  => 'mobile',
            'data'   => [
                'data' => $images['image_localized_but_not_scoped']['data'],
                'attribute' => 'reference_atmosphere_249bf90c-0176-4895-9eed-486fce0fbbe4',
            ],
        ]);
    }

    function it_return_localized_image_when_media_is_localized(
        IdentifiableObjectRepositoryInterface $attributeRepository,
        AssetCollectionValueInterface $assetCollectionValue,
        AssetCode $assetCode,
        FindAssetDetailsInterface $findAssetDetailsQuery,
        AttributeInterface $attribute
    ) {
        $attribute->getReferenceDataName()->willReturn('atmosphere')->shouldBeCalled();
        $attributeRepository->findOneByIdentifier('attribute_collection')->willReturn($attribute)->shouldBeCalled();

        $images = $this->getImages();
        $assetDetails = new AssetDetails(
            AssetIdentifier::fromString('atmosphere_index_b4d9d237-a2e5-4ac5-83c1-22dd25f33202'),
            AssetFamilyIdentifier::fromString('atmosphere'),
            AttributeIdentifier::fromString('reference_atmosphere_249bf90c-0176-4895-9eed-486fce0fbbe4'),
            AssetCode::fromString('index'),
            LabelCollection::fromArray([]),
            $images,
            [],
            false
        );

        $findAssetDetailsQuery->find(AssetFamilyIdentifier::fromString('atmosphere'), $assetCode)->willReturn($assetDetails)->shouldBeCalled();

        $this->normalize($assetCollectionValue, null, ['data_locale' => 'en_GB', 'data_channel' => 'mobile'])->shouldReturn([
            'locale' => 'fr_FR',
            'scope'  => 'mobile',
            'data'   => [
                'data' => $images['image_scoped_but_not_localized']['data'],
                'attribute' => 'reference_atmosphere_249bf90c-0176-4895-9eed-486fce0fbbe4',
            ],
        ]);
    }

    function it_return_not_localized_and_not_scoped_image_when_media_is_not_localized_and_not_scoped(
        IdentifiableObjectRepositoryInterface $attributeRepository,
        AssetCollectionValueInterface $assetCollectionValue,
        AssetCode $assetCode,
        FindAssetDetailsInterface $findAssetDetailsQuery,
        AttributeInterface $attribute
    ) {
        $attribute->getReferenceDataName()->willReturn('atmosphere')->shouldBeCalled();
        $attributeRepository->findOneByIdentifier('attribute_collection')->willReturn($attribute)->shouldBeCalled();

        $images = $this->getImages();
        $assetDetails = new AssetDetails(
            AssetIdentifier::fromString('atmosphere_index_b4d9d237-a2e5-4ac5-83c1-22dd25f33202'),
            AssetFamilyIdentifier::fromString('atmosphere'),
            AttributeIdentifier::fromString('reference_atmosphere_249bf90c-0176-4895-9eed-486fce0fbbe4'),
            AssetCode::fromString('index'),
            LabelCollection::fromArray([]),
            $images,
            [],
            false
        );

        $findAssetDetailsQuery->find(AssetFamilyIdentifier::fromString('atmosphere'), $assetCode)->willReturn($assetDetails)->shouldBeCalled();

        $this->normalize($assetCollectionValue, null, ['data_locale' => 'en_GB', 'data_channel' => 'e-commerce'])->shouldReturn([
            'locale' => 'fr_FR',
            'scope'  => 'mobile',
            'data'   => [
                'data' => $images['image_not_localized_and_not_scoped']['data'],
                'attribute' => 'reference_atmosphere_249bf90c-0176-4895-9eed-486fce0fbbe4',
            ],
        ]);
    }

    private function getImages()
    {
        return [
            'image_localized_and_scoped' => [
                'data' => [
                    'filePath' => 'image_localized_and_scoped.png',
                    'mimeType' => 'image/png',
                ],
                'channel' => 'mobile',
                'locale' => 'fr_FR',
            ],
            'image_localized_but_not_scoped' => [
                'data' => [
                    'filePath' => 'image_localized_but_not_scoped.png',
                    'mimeType' => 'image/png',
                ],
                'channel' => null,
                'locale' => 'fr_FR',
            ],
            'image_scoped_but_not_localized' => [
                'data' => [
                    'filePath' => 'image_scoped_but_not_localized.png',
                    'mimeType' => 'image/png',
                ],
                'channel' => 'mobile',
                'locale' => null,
            ],
            'image_not_localized_and_not_scoped' => [
                'data' => [
                    'filePath' => 'image_not_localized_and_not_scoped.png',
                    'mimeType' => 'image/png',
                ],
                'channel' => null,
                'locale' => null,
            ],
        ];
    }
}
