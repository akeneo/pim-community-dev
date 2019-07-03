<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Normalizer\ExternalApi;

use Akeneo\Pim\Enrichment\Component\Product\Model\ReadValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\ExternalApi\ValuesNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\Product\ProductValueNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Value\MediaValue;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Routing\RouterInterface;

class ValuesNormalizerSpec extends ObjectBehavior
{
    function let(ProductValueNormalizer $productValueNormalizer, RouterInterface $router)
    {
        $this->beConstructedWith($productValueNormalizer, $router);
        $router
            ->generate('pim_api_media_file_download', ['code' => 'a/b/c/file.txt'], Argument::any())
            ->willReturn('http://localhost/api/rest/v1/media-files/a/b/c/file.txt/download');
    }

    function it_is_a_normalizer_of_values_for_the_connector()
    {
        $this->shouldBeAnInstanceOf(ValuesNormalizer::class);
    }

    function it_normalize_values_with_hal_links(ProductValueNormalizer $productValueNormalizer)
    {
        $fileInfo = new FileInfo();
        $fileInfo->setKey('a/b/c/file.txt');

        $scalarValue = ScalarValue::value('color', 'red');
        $localizableScalarValue = ScalarValue::localizableValue('name', 'saymyname', 'en_US');
        $mediaValue = MediaValue::scopableLocalizableValue('image', $fileInfo, 'tablet', 'fr_FR');

        $productValueNormalizer->normalize(ScalarValue::value('color', 'red'), 'standard')->willReturn(
            [
                'scope' => null,
                'locale' => null,
                'data' => 'red'
            ]
        );

        $productValueNormalizer->normalize(ScalarValue::localizableValue('name', 'saymyname', 'en_US'), 'standard')->willReturn(
            [
                'scope' => null,
                'locale' => 'en_US',
                'data' => 'saymyname'
            ]
        );

        $productValueNormalizer->normalize(MediaValue::scopableLocalizableValue('image', $fileInfo, 'tablet', 'fr_FR'), 'standard')->willReturn(
            [
                'scope' => 'tablet',
                'locale' => 'fr_FR',
                'data' => 'a/b/c/file.txt'
            ]
        );

        $valueCollection = new ReadValueCollection([$scalarValue, $localizableScalarValue, $mediaValue]);
        $this->normalize($valueCollection)->shouldReturn(
            [
                'color' => [[
                    'scope' => null,
                    'locale' => null,
                    'data' => 'red'
                ]],
                'name' => [[
                    'scope' => null,
                    'locale' => 'en_US',
                    'data' => 'saymyname'
                ]],
                'image' => [[

                    'scope' => 'tablet',
                    'locale' => 'fr_FR',
                    'data' => 'a/b/c/file.txt',
                    '_links' => [
                        'download' => [
                            'href' => 'http://localhost/api/rest/v1/media-files/a/b/c/file.txt/download'
                        ]
                    ],
                ]]
            ]
        );
    }
}
