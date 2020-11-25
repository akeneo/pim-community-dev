<?php

namespace spec\Akeneo\ReferenceEntity\Infrastructure\Connector\ArrayConverter\StandardToFlat;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\ImageAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\NumberAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\OptionAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\OptionCollectionAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\RecordAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\RecordCollectionAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\TextAttribute;
use Akeneo\ReferenceEntity\Domain\Repository\AttributeRepositoryInterface;
use Akeneo\ReferenceEntity\Infrastructure\Connector\ArrayConverter\StandardToFlat\Record;
use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;
use PhpSpec\ObjectBehavior;

class RecordSpec extends ObjectBehavior
{
    function let(AttributeRepositoryInterface $attributeRepository)
    {
        $this->beConstructedWith($attributeRepository);
    }

    function it_is_an_array_converter()
    {
        $this->shouldImplement(ArrayConverterInterface::class);
    }

    function it_is_a_record_array_converter()
    {
        $this->shouldHaveType(Record::class);
    }

    function it_converts_a_normalized_record_and_skips_identifier()
    {
        $normalizedRecord = [
            'identifier' => 'some_record_identifier_123',
            'code' => 'my_record_code',
            'referenceEntityIdentifier' => 'brand',
            'values' => [],
        ];

        $this->convert($normalizedRecord)->shouldReturn(
            [
                'code' => 'my_record_code',
                'referenceEntityIdentifier' => 'brand',
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

        $normalizedRecord = [
            'identifier' => 'some_record_identifier_123',
            'code' => 'my_record_code',
            'referenceEntityIdentifier' => 'brand',
            'values' => [
                'text123456' => [
                    'attribute' => 'text123456',
                    'locale' => null,
                    'channel' => null,
                    'data' => 'Lorem Ipsum',
                ],
            ],
        ];

        $this->convert($normalizedRecord)->shouldReturn(
            [
                'code' => 'my_record_code',
                'referenceEntityIdentifier' => 'brand',
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

        $normalizedRecord = [
            'identifier' => 'some_record_identifier_123',
            'code' => 'my_record_code',
            'referenceEntityIdentifier' => 'brand',
            'values' => [
                'maxfilesize123456' => [
                    'attribute' => 'maxfilesize123456',
                    'locale' => null,
                    'channel' => null,
                    'data' => '42',
                ],
            ],
        ];

        $this->convert($normalizedRecord)->shouldReturn(
            [
                'code' => 'my_record_code',
                'referenceEntityIdentifier' => 'brand',
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

        $normalizedRecord = [
            'identifier' => 'some_record_identifier_123',
            'code' => 'my_record_code',
            'referenceEntityIdentifier' => 'brand',
            'values' => [
                'typeabcdef' => [
                    'attribute' => 'typeabcdef',
                    'locale' => null,
                    'channel' => null,
                    'data' => 'my_option_code',
                ],
            ],
        ];

        $this->convert($normalizedRecord)->shouldReturn(
            [
                'code' => 'my_record_code',
                'referenceEntityIdentifier' => 'brand',
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

        $normalizedRecord = [
            'identifier' => 'some_record_identifier_123',
            'code' => 'my_record_code',
            'referenceEntityIdentifier' => 'brand',
            'values' => [
                'seasons_987456' => [
                    'attribute' => 'seasons_987456',
                    'locale' => null,
                    'channel' => null,
                    'data' => ['winter', 'spring'],
                ],
            ],
        ];

        $this->convert($normalizedRecord)->shouldReturn(
            [
                'code' => 'my_record_code',
                'referenceEntityIdentifier' => 'brand',
                'seasons' => 'winter,spring',
            ]
        );
    }

    function it_converts_a_record_collection_value(
        AttributeRepositoryInterface $attributeRepository,
        RecordCollectionAttribute $recordCollectionAttribute
    ) {
        $recordCollectionAttribute->getType()->willReturn(RecordCollectionAttribute::ATTRIBUTE_TYPE);
        $recordCollectionAttribute->hasValuePerChannel()->willReturn(false);
        $recordCollectionAttribute->hasValuePerLocale()->willReturn(false);
        $recordCollectionAttribute->getCode()->willReturn(AttributeCode::fromString('brand'));
        $attributeRepository->getByIdentifier(AttributeIdentifier::fromString('brand_987456'))
            ->willReturn($recordCollectionAttribute);

        $normalizedRecord = [
            'identifier' => 'some_record_identifier_123',
            'code' => 'my_record_code',
            'referenceEntityIdentifier' => 'brand',
            'values' => [
                'brand_987456' => [
                    'attribute' => 'brand_987456',
                    'locale' => null,
                    'channel' => null,
                    'data' => ['alessi', 'fatboy'],
                ],
            ],
        ];

        $this->convert($normalizedRecord)->shouldReturn(
            [
                'code' => 'my_record_code',
                'referenceEntityIdentifier' => 'brand',
                'brand' => 'alessi,fatboy',
            ]
        );
    }

    function it_converts_a_record_value(
        AttributeRepositoryInterface $attributeRepository,
        RecordAttribute $recordAttribute
    ) {
        $recordAttribute->getType()->willReturn(RecordAttribute::ATTRIBUTE_TYPE);
        $recordAttribute->hasValuePerChannel()->willReturn(false);
        $recordAttribute->hasValuePerLocale()->willReturn(false);
        $recordAttribute->getCode()->willReturn(AttributeCode::fromString('brand'));
        $attributeRepository->getByIdentifier(AttributeIdentifier::fromString('brand_987456'))
            ->willReturn($recordAttribute);

        $normalizedRecord = [
            'identifier' => 'some_record_identifier_123',
            'code' => 'my_record_code',
            'referenceEntityIdentifier' => 'brand',
            'values' => [
                'brand_987456' => [
                    'attribute' => 'brand_987456',
                    'locale' => null,
                    'channel' => null,
                    'data' => 'alessi'
                ],
            ],
        ];

        $this->convert($normalizedRecord)->shouldReturn(
            [
                'code' => 'my_record_code',
                'referenceEntityIdentifier' => 'brand',
                'brand' => 'alessi',
            ]
        );
    }

    function it_converts_a_media_file_value(
        AttributeRepositoryInterface $attributeRepository,
        ImageAttribute $mediaLinkAttribute
    ) {
        $mediaLinkAttribute->getType()->willReturn(ImageAttribute::ATTRIBUTE_TYPE);
        $mediaLinkAttribute->hasValuePerChannel()->willReturn(false);
        $mediaLinkAttribute->hasValuePerLocale()->willReturn(false);
        $mediaLinkAttribute->getCode()->willReturn(AttributeCode::fromString('image'));

        $attributeRepository->getByIdentifier(AttributeIdentifier::fromString('image_123456'))
            ->willReturn($mediaLinkAttribute);

        $normalizedRecord = [
            'identifier' => 'some_record_identifier_123',
            'code' => 'my_record_code',
            'referenceEntityIdentifier' => 'brand',
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

        $this->convert($normalizedRecord)->shouldReturn(
            [
                'code' => 'my_record_code',
                'referenceEntityIdentifier' => 'brand',
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

        $normalizedRecord = [
            'identifier' => 'some_record_identifier_123',
            'code' => 'my_record_code',
            'referenceEntityIdentifier' => 'brand',
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

        $this->convert($normalizedRecord)->shouldReturn(
            [
                'code' => 'my_record_code',
                'referenceEntityIdentifier' => 'brand',
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

        $normalizedRecord = [
            'identifier' => 'some_record_identifier_123',
            'code' => 'my_record_code',
            'referenceEntityIdentifier' => 'brand',
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

        $this->convert($normalizedRecord)->shouldReturn(
            [
                'code' => 'my_record_code',
                'referenceEntityIdentifier' => 'brand',
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

        $normalizedRecord = [
            'identifier' => 'some_record_identifier_123',
            'code' => 'my_record_code',
            'referenceEntityIdentifier' => 'brand',
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

        $this->convert($normalizedRecord)->shouldReturn(
            [
                'code' => 'my_record_code',
                'referenceEntityIdentifier' => 'brand',
                'description-en_US-ecommerce' => 'Some great description',
                'description-fr_FR-ecommerce' => 'Super description',
                'description-en_US-mobile' => 'Short desc',
            ]
        );
    }
}
