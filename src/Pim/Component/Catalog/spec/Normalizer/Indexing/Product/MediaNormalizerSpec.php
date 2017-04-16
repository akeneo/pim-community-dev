<?php

namespace spec\Pim\Component\Catalog\Normalizer\Indexing\Product;

use Akeneo\Component\FileStorage\Model\FileInfoInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Pim\Component\Catalog\Normalizer\Indexing\Product\MediaNormalizer;
use Pim\Component\Catalog\ProductValue\MediaProductValueInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class MediaNormalizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(MediaNormalizer::class);
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement(NormalizerInterface::class);
    }

    function it_support_media_product_value(
        ProductValueInterface $numberValue,
        MediaProductValueInterface $mediaValue,
        AttributeInterface $numberAttribute,
        AttributeInterface $mediaAttribute
    ) {
        $mediaValue->getAttribute()->willReturn($mediaAttribute);
        $numberValue->getAttribute()->willReturn($numberAttribute);

        $this->supportsNormalization(new \stdClass(), 'indexing')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'whatever')->shouldReturn(false);

        $this->supportsNormalization($mediaValue, 'indexing')->shouldReturn(true);
        $this->supportsNormalization($numberValue, 'whatever')->shouldReturn(false);
        $this->supportsNormalization($numberValue, 'indexing')->shouldReturn(false);
    }

    function it_normalizes_a_media_product_value_with_no_locale_and_no_channel(
        MediaProductValueInterface $mediaValue,
        AttributeInterface $mediaAttribute,
        FileInfoInterface $fileInfo
    ) {
        $mediaValue->getAttribute()->willReturn($mediaAttribute);
        $mediaValue->getLocale()->willReturn(null);
        $mediaValue->getScope()->willReturn(null);
        $mediaValue->getData()->willReturn($fileInfo);

        $fileInfo->getExtension()->willReturn('jpg');
        $fileInfo->getKey()->willReturn('a/relative/path/to/akeneo.jpg');
        $fileInfo->getHash()->willReturn('a_hash_key');
        $fileInfo->getMimeType()->willReturn('image/jpeg');
        $fileInfo->getOriginalFilename()->willReturn('akeneo.jpg');
        $fileInfo->getSize()->willReturn('42');
        $fileInfo->getStorage()->willReturn('catalogStorage');

        $mediaAttribute->getCode()->willReturn('an_image');
        $mediaAttribute->getBackendType()->willReturn('media');

        $this->normalize($mediaValue, 'indexing')->shouldReturn([
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
        ProductValueInterface $mediaValue,
        AttributeInterface $mediaAttribute,
        FileInfoInterface $fileInfo
    ) {
        $mediaValue->getAttribute()->willReturn($mediaAttribute);
        $mediaValue->getLocale()->willReturn('fr_FR');
        $mediaValue->getScope()->willReturn(null);
        $mediaValue->getData()->willReturn($fileInfo);

        $fileInfo->getExtension()->willReturn('jpg');
        $fileInfo->getKey()->willReturn('a/relative/path/to/akeneo.jpg');
        $fileInfo->getHash()->willReturn('a_hash_key');
        $fileInfo->getMimeType()->willReturn('image/jpeg');
        $fileInfo->getOriginalFilename()->willReturn('akeneo.jpg');
        $fileInfo->getSize()->willReturn('42');
        $fileInfo->getStorage()->willReturn('catalogStorage');

        $mediaAttribute->getCode()->willReturn('an_image');
        $mediaAttribute->getBackendType()->willReturn('media');

        $this->normalize($mediaValue, 'indexing')->shouldReturn([
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
        MediaProductValueInterface $mediaValue,
        AttributeInterface $mediaAttribute,
        FileInfoInterface $fileInfo
    ) {
        $mediaValue->getAttribute()->willReturn($mediaAttribute);
        $mediaValue->getLocale()->willReturn(null);
        $mediaValue->getScope()->willReturn('ecommerce');
        $mediaValue->getData()->willReturn($fileInfo);

        $fileInfo->getExtension()->willReturn('jpg');
        $fileInfo->getKey()->willReturn('a/relative/path/to/akeneo.jpg');
        $fileInfo->getHash()->willReturn('a_hash_key');
        $fileInfo->getMimeType()->willReturn('image/jpeg');
        $fileInfo->getOriginalFilename()->willReturn('akeneo.jpg');
        $fileInfo->getSize()->willReturn('42');
        $fileInfo->getStorage()->willReturn('catalogStorage');

        $mediaAttribute->getCode()->willReturn('an_image');
        $mediaAttribute->getBackendType()->willReturn('media');

        $this->normalize($mediaValue, 'indexing')->shouldReturn([
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
        MediaProductValueInterface $mediaValue,
        AttributeInterface $mediaAttribute,
        FileInfoInterface $fileInfo
    ) {
        $mediaValue->getAttribute()->willReturn($mediaAttribute);
        $mediaValue->getLocale()->willReturn('fr_FR');
        $mediaValue->getScope()->willReturn('ecommerce');
        $mediaValue->getData()->willReturn($fileInfo);

        $fileInfo->getExtension()->willReturn('jpg');
        $fileInfo->getKey()->willReturn('a/relative/path/to/akeneo.jpg');
        $fileInfo->getHash()->willReturn('a_hash_key');
        $fileInfo->getMimeType()->willReturn('image/jpeg');
        $fileInfo->getOriginalFilename()->willReturn('akeneo.jpg');
        $fileInfo->getSize()->willReturn('42');
        $fileInfo->getStorage()->willReturn('catalogStorage');

        $mediaAttribute->getCode()->willReturn('an_image');
        $mediaAttribute->getBackendType()->willReturn('media');

        $this->normalize($mediaValue, 'indexing')->shouldReturn([
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
        MediaProductValueInterface $mediaValue,
        AttributeInterface $mediaAttribute
    ) {
        $mediaValue->getAttribute()->willReturn($mediaAttribute);
        $mediaValue->getLocale()->willReturn('fr_FR');
        $mediaValue->getScope()->willReturn('ecommerce');
        $mediaValue->getData()->willReturn(null);

        $mediaAttribute->getCode()->willReturn('an_image');
        $mediaAttribute->getBackendType()->willReturn('media');

        $this->normalize($mediaValue, 'indexing')->shouldReturn([
            'an_image-media' => [
                'ecommerce' => [
                    'fr_FR' => null,
                ],
            ],
        ]);
    }
}
