<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value;

use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value\MediaNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value\ValueCollectionNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Value\MediaValueInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class MediaNormalizerSpec extends ObjectBehavior
{
    function let(GetAttributes $getAttributes)
    {
        $this->beConstructedWith($getAttributes);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(MediaNormalizer::class);
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement(NormalizerInterface::class);
    }

    function it_support_media_product_value_for_both_indexing_formats(
        ValueInterface $numberValue,
        MediaValueInterface $mediaValue
    ) {
        $this->supportsNormalization(new \stdClass(), 'whatever')->shouldReturn(false);

        $this->supportsNormalization($numberValue, 'whatever')->shouldReturn(false);

        $this->supportsNormalization(new \stdClass(), ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->shouldReturn(false);
        $this->supportsNormalization($mediaValue, ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->shouldReturn(true);
        $this->supportsNormalization($numberValue, ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->shouldReturn(false);
    }

    function it_normalizes_a_media_product_value_with_no_locale_and_no_channel(
        MediaValueInterface $mediaValue,
        FileInfoInterface $fileInfo,
        GetAttributes $getAttributes
    ) {
        $mediaValue->getAttributeCode()->willReturn('an_image');
        $mediaValue->getLocaleCode()->willReturn(null);
        $mediaValue->getScopeCode()->willReturn(null);
        $mediaValue->getData()->willReturn($fileInfo);

        $fileInfo->getExtension()->willReturn('jpg');
        $fileInfo->getKey()->willReturn('a/relative/path/to/akeneo.jpg');
        $fileInfo->getHash()->willReturn('a_hash_key');
        $fileInfo->getMimeType()->willReturn('image/jpeg');
        $fileInfo->getOriginalFilename()->willReturn('akeneo.jpg');
        $fileInfo->getSize()->willReturn('42');
        $fileInfo->getStorage()->willReturn('catalogStorage');

        $getAttributes->forCode('an_image')->willReturn(new Attribute(
            'an_image',
            'pim_catalog_file',
            [],
            false,
            false,
            null,
            true,
            'media',
            []
        ));

        $this->normalize($mediaValue, ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn([
            'an_image-media' => [
                '<all_channels>' => [
                    '<all_locales>' => [
                        'extension'         => 'jpg',
                        'key'               => 'a/relative/path/to/akeneo.jpg',
                        'hash'              => 'a_hash_key',
                        'mime_type'         => 'image/jpeg',
                        'original_filename' => 'akeneo.jpg',
                        'size'              => '42',
                        'storage'           => 'catalogStorage',
                    ],
                ],
            ],
        ]);
    }

    function it_normalizes_a_media_product_value_with_locale_and_no_scope(
        ValueInterface $mediaValue,
        FileInfoInterface $fileInfo,
        GetAttributes $getAttributes
    ) {
        $mediaValue->getAttributeCode()->willReturn('an_image');
        $mediaValue->getLocaleCode()->willReturn('fr_FR');
        $mediaValue->getScopeCode()->willReturn(null);
        $mediaValue->getData()->willReturn($fileInfo);

        $fileInfo->getExtension()->willReturn('jpg');
        $fileInfo->getKey()->willReturn('a/relative/path/to/akeneo.jpg');
        $fileInfo->getHash()->willReturn('a_hash_key');
        $fileInfo->getMimeType()->willReturn('image/jpeg');
        $fileInfo->getOriginalFilename()->willReturn('akeneo.jpg');
        $fileInfo->getSize()->willReturn('42');
        $fileInfo->getStorage()->willReturn('catalogStorage');

        $getAttributes->forCode('an_image')->willReturn(new Attribute(
            'an_image',
            'pim_catalog_file',
            [],
            true,
            false,
            null,
            true,
            'media',
            []
        ));

        $this->normalize($mediaValue, ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn([
            'an_image-media' => [
                '<all_channels>' => [
                    'fr_FR' => [
                        'extension'         => 'jpg',
                        'key'               => 'a/relative/path/to/akeneo.jpg',
                        'hash'              => 'a_hash_key',
                        'mime_type'         => 'image/jpeg',
                        'original_filename' => 'akeneo.jpg',
                        'size'              => '42',
                        'storage'           => 'catalogStorage',
                    ],
                ],
            ],
        ]);
    }

    function it_normalizes_a_media_product_value_with_scope_and_no_locale(
        MediaValueInterface $mediaValue,
        FileInfoInterface $fileInfo,
        GetAttributes $getAttributes
    ) {
        $mediaValue->getAttributeCode()->willReturn('an_image');
        $mediaValue->getLocaleCode()->willReturn(null);
        $mediaValue->getScopeCode()->willReturn('ecommerce');
        $mediaValue->getData()->willReturn($fileInfo);

        $fileInfo->getExtension()->willReturn('jpg');
        $fileInfo->getKey()->willReturn('a/relative/path/to/akeneo.jpg');
        $fileInfo->getHash()->willReturn('a_hash_key');
        $fileInfo->getMimeType()->willReturn('image/jpeg');
        $fileInfo->getOriginalFilename()->willReturn('akeneo.jpg');
        $fileInfo->getSize()->willReturn('42');
        $fileInfo->getStorage()->willReturn('catalogStorage');

        $getAttributes->forCode('an_image')->willReturn(new Attribute(
            'an_image',
            'pim_catalog_file',
            [],
            false,
            true,
            null,
            true,
            'media',
            []
        ));

        $this->normalize($mediaValue, ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn([
            'an_image-media' => [
                'ecommerce' => [
                    '<all_locales>' => [
                        'extension'         => 'jpg',
                        'key'               => 'a/relative/path/to/akeneo.jpg',
                        'hash'              => 'a_hash_key',
                        'mime_type'         => 'image/jpeg',
                        'original_filename' => 'akeneo.jpg',
                        'size'              => '42',
                        'storage'           => 'catalogStorage',
                    ],
                ],
            ],
        ]);
    }

    function it_normalizes_a_media_product_value_with_locale_and_scope(
        MediaValueInterface $mediaValue,
        FileInfoInterface $fileInfo,
        GetAttributes $getAttributes
    ) {
        $mediaValue->getAttributeCode()->willReturn('an_image');
        $mediaValue->getLocaleCode()->willReturn('fr_FR');
        $mediaValue->getScopeCode()->willReturn('ecommerce');
        $mediaValue->getData()->willReturn($fileInfo);

        $fileInfo->getExtension()->willReturn('jpg');
        $fileInfo->getKey()->willReturn('a/relative/path/to/akeneo.jpg');
        $fileInfo->getHash()->willReturn('a_hash_key');
        $fileInfo->getMimeType()->willReturn('image/jpeg');
        $fileInfo->getOriginalFilename()->willReturn('akeneo.jpg');
        $fileInfo->getSize()->willReturn('42');
        $fileInfo->getStorage()->willReturn('catalogStorage');

        $getAttributes->forCode('an_image')->willReturn(new Attribute(
            'an_image',
            'pim_catalog_file',
            [],
            true,
            true,
            null,
            true,
            'media',
            []
        ));

        $this->normalize($mediaValue, ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn([
            'an_image-media' => [
                'ecommerce' => [
                    'fr_FR' => [
                        'extension'         => 'jpg',
                        'key'               => 'a/relative/path/to/akeneo.jpg',
                        'hash'              => 'a_hash_key',
                        'mime_type'         => 'image/jpeg',
                        'original_filename' => 'akeneo.jpg',
                        'size'              => '42',
                        'storage'           => 'catalogStorage',
                    ],
                ],
            ],
        ]);
    }

    function it_should_normalize_an_empty_product_value(
        MediaValueInterface $mediaValue,
        GetAttributes $getAttributes
    ) {
        $mediaValue->getAttributeCode()->willReturn('an_image');
        $mediaValue->getLocaleCode()->willReturn('fr_FR');
        $mediaValue->getScopeCode()->willReturn('ecommerce');
        $mediaValue->getData()->willReturn(null);

        $getAttributes->forCode('an_image')->willReturn(new Attribute(
            'an_image',
            'pim_catalog_file',
            [],
            true,
            true,
            null,
            true,
            'media',
            []
        ));

        $this->normalize($mediaValue, ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->shouldReturn([
            'an_image-media' => [
                'ecommerce' => [
                    'fr_FR' => null,
                ],
            ],
        ]);
    }
}
