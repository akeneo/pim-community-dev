<?php

namespace Specification\Akeneo\Pim\Enrichment\AssetManager\Component\Connector\ArrayConverter\StandardToFlat;

use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaFileAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaLink\Prefix;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaLink\Suffix;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaLinkAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\NumberAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\OptionAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\OptionCollectionAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\TextAttribute;
use Akeneo\AssetManager\Domain\Repository\AttributeRepositoryInterface;
use Akeneo\Pim\Enrichment\AssetManager\Component\Connector\ArrayConverter\StandardToFlat\Asset;
use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;
use PhpSpec\ObjectBehavior;

class AssetSpec extends ObjectBehavior
{
    function let(AttributeRepositoryInterface $attributeRepository)
    {
        $this->beConstructedWith($attributeRepository);
    }

    function it_is_an_array_converter()
    {
        $this->shouldImplement(ArrayConverterInterface::class);
    }

    function it_is_an_asset_array_converter()
    {
        $this->shouldHaveType(Asset::class);
    }

    function it_converts_a_normalized_asset_and_skips_identifier()
    {
        $normalizedAsset = [
            'identifier' => 'some_asset_identifier_123',
            'code' => 'my_asset_code',
            'assetFamilyIdentifier' => 'packshot',
            'values' => [],
        ];

        $this->convert($normalizedAsset)->shouldReturn(
            [
                'code' => 'my_asset_code',
                'assetFamilyIdentifier' => 'packshot',
            ]
        );
    }

    function it_converts_a_text_value(
        AttributeRepositoryInterface $attributeRepository,
        TextAttribute $textAttribute
    ) {
        $textAttribute->getType()->willReturn(TextAttribute::ATTRIBUTE_TYPE);
        $textAttribute->hasValuePerChannel()->willReturn(false);
        $textAttribute->hasValuePerLocale()->willReturn(false);
        $textAttribute->getCode()->willReturn(AttributeCode::fromString('name'));
        $attributeRepository->getByIdentifier(AttributeIdentifier::fromString('text123456'))
            ->willReturn($textAttribute);

        $normalizedAsset = [
            'identifier' => 'some_asset_identifier_123',
            'code' => 'my_asset_code',
            'assetFamilyIdentifier' => 'packshot',
            'values' => [
                'text123456' => [
                    'attribute' => 'text123456',
                    'locale' => null,
                    'channel' => null,
                    'data' => 'Lorem Ipsum',
                ],
            ],
        ];

        $this->convert($normalizedAsset)->shouldReturn(
            [
                'code' => 'my_asset_code',
                'assetFamilyIdentifier' => 'packshot',
                'name' => 'Lorem Ipsum',
            ]
        );
    }

    function it_converts_a_number_value(
        AttributeRepositoryInterface $attributeRepository,
        NumberAttribute $numberAttribute
    ) {
        $numberAttribute->getType()->willReturn(NumberAttribute::ATTRIBUTE_TYPE);
        $numberAttribute->hasValuePerChannel()->willReturn(false);
        $numberAttribute->hasValuePerLocale()->willReturn(false);
        $numberAttribute->getCode()->willReturn(AttributeCode::fromString('max_file_size'));
        $attributeRepository->getByIdentifier(AttributeIdentifier::fromString('maxfilesize123456'))
                            ->willReturn($numberAttribute);

        $normalizedAsset = [
            'identifier' => 'some_asset_identifier_123',
            'code' => 'my_asset_code',
            'assetFamilyIdentifier' => 'packshot',
            'values' => [
                'maxfilesize123456' => [
                    'attribute' => 'maxfilesize123456',
                    'locale' => null,
                    'channel' => null,
                    'data' => '42',
                ],
            ],
        ];

        $this->convert($normalizedAsset)->shouldReturn(
            [
                'code' => 'my_asset_code',
                'assetFamilyIdentifier' => 'packshot',
                'max_file_size' => '42',
            ]
        );
    }

    function it_converts_an_option_value(
        AttributeRepositoryInterface $attributeRepository,
        OptionAttribute $optionAttribute
    ) {
        $optionAttribute->getType()->willReturn(OptionAttribute::ATTRIBUTE_TYPE);
        $optionAttribute->hasValuePerChannel()->willReturn(false);
        $optionAttribute->hasValuePerLocale()->willReturn(false);
        $optionAttribute->getCode()->willReturn(AttributeCode::fromString('type'));
        $attributeRepository->getByIdentifier(AttributeIdentifier::fromString('typeabcdef'))
                            ->willReturn($optionAttribute);

        $normalizedAsset = [
            'identifier' => 'some_asset_identifier_123',
            'code' => 'my_asset_code',
            'assetFamilyIdentifier' => 'packshot',
            'values' => [
                'typeabcdef' => [
                    'attribute' => 'typeabcdef',
                    'locale' => null,
                    'channel' => null,
                    'data' => 'my_option_code',
                ],
            ],
        ];

        $this->convert($normalizedAsset)->shouldReturn(
            [
                'code' => 'my_asset_code',
                'assetFamilyIdentifier' => 'packshot',
                'type' => 'my_option_code',
            ]
        );
    }

    function it_converts_an_option_collection_value(
        AttributeRepositoryInterface $attributeRepository,
        OptionCollectionAttribute $optionCollectionAttribute
    ) {
        $optionCollectionAttribute->getType()->willReturn(OptionCollectionAttribute::ATTRIBUTE_TYPE);
        $optionCollectionAttribute->hasValuePerChannel()->willReturn(false);
        $optionCollectionAttribute->hasValuePerLocale()->willReturn(false);
        $optionCollectionAttribute->getCode()->willReturn(AttributeCode::fromString('seasons'));
        $attributeRepository->getByIdentifier(AttributeIdentifier::fromString('seasons_987456'))
                            ->willReturn($optionCollectionAttribute);

        $normalizedAsset = [
            'identifier' => 'some_asset_identifier_123',
            'code' => 'my_asset_code',
            'assetFamilyIdentifier' => 'packshot',
            'values' => [
                'seasons_987456' => [
                    'attribute' => 'seasons_987456',
                    'locale' => null,
                    'channel' => null,
                    'data' => ['winter', 'spring'],
                ],
            ],
        ];

        $this->convert($normalizedAsset)->shouldReturn(
            [
                'code' => 'my_asset_code',
                'assetFamilyIdentifier' => 'packshot',
                'seasons' => 'winter,spring',
            ]
        );
    }

    function it_converts_a_media_link_value(
        AttributeRepositoryInterface $attributeRepository,
        MediaLinkAttribute $mediaLinkAttribute
    ) {
        $mediaLinkAttribute->getType()->willReturn(MediaLinkAttribute::ATTRIBUTE_TYPE);
        $mediaLinkAttribute->hasValuePerChannel()->willReturn(false);
        $mediaLinkAttribute->hasValuePerLocale()->willReturn(false);
        $mediaLinkAttribute->getCode()->willReturn(AttributeCode::fromString('youtube_link'));

        $attributeRepository->getByIdentifier(AttributeIdentifier::fromString('youtube_link123456'))
            ->willReturn($mediaLinkAttribute);

        $normalizedAsset = [
            'identifier' => 'some_asset_identifier_123',
            'code' => 'my_asset_code',
            'assetFamilyIdentifier' => 'packshot',
            'values' => [
                'youtube_link123456' => [
                    'attribute' => 'youtube_link123456',
                    'locale' => null,
                    'channel' => null,
                    'data' => 'aZeTFa789',
                ],
            ],
        ];

        $this->convert($normalizedAsset)->shouldReturn(
            [
                'code' => 'my_asset_code',
                'assetFamilyIdentifier' => 'packshot',
                'youtube_link' => 'aZeTFa789',
            ]
        );
    }

    function it_converts_a_media_link_value_with_prefix_suffix(
        AttributeRepositoryInterface $attributeRepository,
        MediaLinkAttribute $mediaLinkAttribute
    ) {
        $mediaLinkAttribute->getType()->willReturn(MediaLinkAttribute::ATTRIBUTE_TYPE);
        $mediaLinkAttribute->hasValuePerChannel()->willReturn(false);
        $mediaLinkAttribute->hasValuePerLocale()->willReturn(false);
        $mediaLinkAttribute->getCode()->willReturn(AttributeCode::fromString('youtube_link'));
        $mediaLinkAttribute->getPrefix()->willReturn(Prefix::fromString('https://youtube.com/'));
        $mediaLinkAttribute->getSuffix()->willReturn(Suffix::fromString('/test'));

        $attributeRepository->getByIdentifier(AttributeIdentifier::fromString('youtube_link123456'))
            ->willReturn($mediaLinkAttribute);

        $normalizedAsset = [
            'identifier' => 'some_asset_identifier_123',
            'code' => 'my_asset_code',
            'assetFamilyIdentifier' => 'packshot',
            'values' => [
                'youtube_link123456' => [
                    'attribute' => 'youtube_link123456',
                    'locale' => null,
                    'channel' => null,
                    'data' => 'aZeTFa789',
                ],
            ],
        ];

        $this->convert($normalizedAsset, ['with_prefix_suffix' => true])->shouldReturn(
            [
                'code' => 'my_asset_code',
                'assetFamilyIdentifier' => 'packshot',
                'youtube_link' => 'https://youtube.com/aZeTFa789/test',
            ]
        );
    }

    function it_converts_a_media_file_value(
        AttributeRepositoryInterface $attributeRepository,
        MediaFileAttribute $mediaFileAttribute
    ) {
        $mediaFileAttribute->getType()->willReturn(MediaFileAttribute::ATTRIBUTE_TYPE);
        $mediaFileAttribute->hasValuePerChannel()->willReturn(false);
        $mediaFileAttribute->hasValuePerLocale()->willReturn(false);
        $mediaFileAttribute->getCode()->willReturn(AttributeCode::fromString('image'));

        $attributeRepository->getByIdentifier(AttributeIdentifier::fromString('image_123456'))
            ->willReturn($mediaFileAttribute);

        $normalizedAsset = [
            'identifier' => 'some_asset_identifier_123',
            'code' => 'my_asset_code',
            'assetFamilyIdentifier' => 'packshot',
            'values' => [
                'image_123456' => [
                    'attribute' => 'image_123456',
                    'locale' => null,
                    'channel' => null,
                    'data' => [
                        'filePath' => '1/2/3/jambon_123456.jpg',
                        'originalFilename' => 'jambon.jpg',
                        'size' => 4096,
                        'mimeType' => 'image/jpeg',
                        'extension' => 'jpg',
                        'updatedAt' => '2020-01-01',
                    ]
                ],
            ],
        ];

        $this->convert($normalizedAsset)->shouldReturn(
            [
                'code' => 'my_asset_code',
                'assetFamilyIdentifier' => 'packshot',
                'image' => '1/2/3/jambon_123456.jpg',
            ]
        );
    }

    function it_generates_keys_for_localizable_attributes(
        AttributeRepositoryInterface $attributeRepository,
        TextAttribute $textAttribute
    ) {
        $textAttribute->getType()->willReturn(TextAttribute::ATTRIBUTE_TYPE);
        $textAttribute->hasValuePerChannel()->willReturn(false);
        $textAttribute->hasValuePerLocale()->willReturn(true);
        $textAttribute->getCode()->willReturn(AttributeCode::fromString('labels'));
        $attributeRepository->getByIdentifier(AttributeIdentifier::fromString('labels_123456'))
            ->shouldBeCalledOnce()->willReturn($textAttribute);

        $normalizedAsset = [
            'identifier' => 'some_asset_identifier_123',
            'code' => 'my_asset_code',
            'assetFamilyIdentifier' => 'packshot',
            'values' => [
                'text123456_en_US' => [
                    'attribute' => 'labels_123456',
                    'locale' => 'en_US',
                    'channel' => null,
                    'data' => 'English label',
                ],
                'text123456_fr_FR' => [
                    'attribute' => 'labels_123456',
                    'locale' => 'fr_FR',
                    'channel' => null,
                    'data' => 'Label français',
                ],
            ],
        ];

        $this->convert($normalizedAsset)->shouldReturn(
            [
                'code' => 'my_asset_code',
                'assetFamilyIdentifier' => 'packshot',
                'labels-en_US' => 'English label',
                'labels-fr_FR' => 'Label français',
            ]
        );
    }

    function it_generates_keys_for_scopable_attributes(
        AttributeRepositoryInterface $attributeRepository,
        TextAttribute $textAttribute
    ) {
        $textAttribute->getType()->willReturn(TextAttribute::ATTRIBUTE_TYPE);
        $textAttribute->hasValuePerChannel()->willReturn(true);
        $textAttribute->hasValuePerLocale()->willReturn(false);
        $textAttribute->getCode()->willReturn(AttributeCode::fromString('description'));
        $attributeRepository->getByIdentifier(AttributeIdentifier::fromString('description_abcdef'))
                            ->shouldBeCalledOnce()->willReturn($textAttribute);

        $normalizedAsset = [
            'identifier' => 'some_asset_identifier_123',
            'code' => 'my_asset_code',
            'assetFamilyIdentifier' => 'packshot',
            'values' => [
                'description_abcdef_ecommerce' => [
                    'attribute' => 'description_abcdef',
                    'locale' => null,
                    'channel' => 'ecommerce',
                    'data' => 'Some great description',
                ],
                'scopable_abcdef_mobile' => [
                    'attribute' => 'description_abcdef',
                    'locale' => null,
                    'channel' => 'mobile',
                    'data' => 'Short desc',
                ],
            ],
        ];

        $this->convert($normalizedAsset)->shouldReturn(
            [
                'code' => 'my_asset_code',
                'assetFamilyIdentifier' => 'packshot',
                'description-ecommerce' => 'Some great description',
                'description-mobile' => 'Short desc',
            ]
        );
    }

    function it_generates_keys_for_scopable_and_localizable_attributes(
        AttributeRepositoryInterface $attributeRepository,
        TextAttribute $textAttribute
    ) {
        $textAttribute->getType()->willReturn(TextAttribute::ATTRIBUTE_TYPE);
        $textAttribute->hasValuePerChannel()->willReturn(true);
        $textAttribute->hasValuePerLocale()->willReturn(true);
        $textAttribute->getCode()->willReturn(AttributeCode::fromString('description'));
        $attributeRepository->getByIdentifier(AttributeIdentifier::fromString('description_abcdef'))
                            ->shouldBeCalledOnce()->willReturn($textAttribute);

        $normalizedAsset = [
            'identifier' => 'some_asset_identifier_123',
            'code' => 'my_asset_code',
            'assetFamilyIdentifier' => 'packshot',
            'values' => [
                'description_abcdef_ecommerce_en_US' => [
                    'attribute' => 'description_abcdef',
                    'locale' => 'en_US',
                    'channel' => 'ecommerce',
                    'data' => 'Some great description',
                ],
                'description_abcdef_ecommerce_fr_FR' => [
                    'attribute' => 'description_abcdef',
                    'locale' => 'fr_FR',
                    'channel' => 'ecommerce',
                    'data' => 'Super description',
                ],
                'scopable_abcdef_mobile_fr_FR' => [
                    'attribute' => 'description_abcdef',
                    'locale' => 'en_US',
                    'channel' => 'mobile',
                    'data' => 'Short desc',
                ],
            ],
        ];

        $this->convert($normalizedAsset)->shouldReturn(
            [
                'code' => 'my_asset_code',
                'assetFamilyIdentifier' => 'packshot',
                'description-en_US-ecommerce' => 'Some great description',
                'description-fr_FR-ecommerce' => 'Super description',
                'description-en_US-mobile' => 'Short desc',
            ]
        );
    }
}
