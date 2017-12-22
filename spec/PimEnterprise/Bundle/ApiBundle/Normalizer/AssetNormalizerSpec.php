<?php

namespace spec\PimEnterprise\Bundle\ApiBundle\Normalizer;

use Doctrine\Common\Collections\Collection;
use PhpSpec\ObjectBehavior;
use PimEnterprise\Bundle\ApiBundle\Normalizer\AssetNormalizer;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;
use PimEnterprise\Component\ProductAsset\Model\ReferenceInterface;
use PimEnterprise\Component\ProductAsset\Model\VariationInterface;
use Prophecy\Argument;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class AssetNormalizerSpec extends ObjectBehavior
{
    function let(
        NormalizerInterface $standardAssetNormalizer,
        NormalizerInterface $variationNormalizer,
        NormalizerInterface $referenceNormalizer,
        RouterInterface $router
    ) {
        $this->beConstructedWith($standardAssetNormalizer, $variationNormalizer, $referenceNormalizer, $router);
    }

    function it_is_an_asset_normalizer()
    {
        $this->shouldHaveType(AssetNormalizer::class);
        $this->shouldImplement(NormalizerInterface::class);
    }

    function it_supports_asset_normalization(AssetInterface $asset, \stdClass $object)
    {
        $this->supportsNormalization($object, 'external_api')->shouldReturn(false);
        $this->supportsNormalization($asset, 'foo_bar')->shouldReturn(false);
        $this->supportsNormalization($asset, 'external_api')->shouldReturn(true);
    }

    function it_normalizes_a_non_localizable_asset(
        $standardAssetNormalizer,
        $variationNormalizer,
        $referenceNormalizer,
        $router,
        AssetInterface $asset,
        VariationInterface $variation,
        Collection $references,
        \Iterator $referencesIterator,
        ReferenceInterface $reference
    ) {
        $standardAssetNormalizer->normalize($asset, 'standard', [])->willReturn(
            [
                'code' => 'ham',
                'localized' => false,
                'description' => 'Ham is better with jam!',
                'end_of_use' => '2041-01-01T00:00:00+0200',
                'categories' => [
                    'meat',
                ],
                'tags' => [
                    'cooking',
                ],
            ]
        );

        $asset->getCode()->willReturn('ham');
        $asset->getVariations()->willReturn([$variation]);
        $asset->getReferences()->willReturn($references);

        $variationNormalizer->normalize($variation, 'external_api', [])->willReturn([
            '_link' => [
                'download' => [
                    'href' => 'http://akeneo.ham.com/rest/v1/assets/ham/variation-files/ecommerce/no-locale/download',
                ],
            ],
            'locale' => null,
            'channel' => 'ecommerce',
            'code' => 'relative/path/to/the/reference_image_variation.jpg',
        ]);

        $references->getIterator()->willReturn($referencesIterator);
        $referencesIterator->rewind()->shouldBeCalled();
        $referencesIterator->valid()->willReturn(true, false);
        $referencesIterator->current()->willReturn($reference);
        $referencesIterator->next()->shouldBeCalled();

        $referenceNormalizer->normalize($reference, 'external_api', [])->willReturn([
            '_link' => [
                'download' => [
                    'href' => 'http://akeneo.ham.com/rest/v1/assets/ham/reference-files/no-locale/download',
                ],
            ],
            'locale' => null,
            'code' => 'relative/path/to/the/reference_image.jpg',
        ]);

        $variation->getAsset()->willReturn($asset);
        $reference->getAsset()->willReturn($asset);

        $router->generate(
            'pimee_api_asset_variation_get',
            [
                'code' => 'ham',
                'channelCode' => 'ecommerce',
                'localeCode' => 'no-locale',
            ],
            UrlGeneratorInterface::ABSOLUTE_URL
        )->willReturn('http://akeneo.ham.com/rest/v1/assets/ham/variation-files/ecommerce/no-locale');

        $router->generate(
            'pimee_api_asset_reference_get',
            [
                'code' => 'ham',
                'localeCode' => 'no-locale',
            ],
            UrlGeneratorInterface::ABSOLUTE_URL
        )->willReturn('http://akeneo.ham.com/rest/v1/assets/ham/reference-files/no-locale');

        $this->normalize($asset, 'external_api', [])->shouldReturn(
            [
                'code' => 'ham',
                'localized' => false,
                'description' => 'Ham is better with jam!',
                'end_of_use' => '2041-01-01T00:00:00+0200',
                'categories' => [
                    'meat',
                ],
                'tags' => [
                    'cooking',
                ],
                'variation_files' => [
                    [
                        '_link' => [
                            'download' => [
                                'href' => 'http://akeneo.ham.com/rest/v1/assets/ham/variation-files/ecommerce/no-locale/download',
                            ],
                            'self' => [
                                'href' => 'http://akeneo.ham.com/rest/v1/assets/ham/variation-files/ecommerce/no-locale',
                            ],
                        ],
                        'locale' => null,
                        'channel' => 'ecommerce',
                        'code' => 'relative/path/to/the/reference_image_variation.jpg',
                    ],
                ],
                'reference_files' => [
                    [
                        '_link' => [
                            'download' => [
                                'href' => 'http://akeneo.ham.com/rest/v1/assets/ham/reference-files/no-locale/download',
                            ],
                            'self' => [
                                'href' => 'http://akeneo.ham.com/rest/v1/assets/ham/reference-files/no-locale',
                            ],
                        ],
                        'locale' => null,
                        'code' => 'relative/path/to/the/reference_image.jpg',
                    ],
                ],
            ]
        );
    }

    function it_normalizes_a_localizable_asset(
        $standardAssetNormalizer,
        $variationNormalizer,
        $referenceNormalizer,
        $router,
        AssetInterface $asset,
        VariationInterface $variation,
        Collection $references,
        \Iterator $referencesIterator,
        ReferenceInterface $reference
    ) {
        $standardAssetNormalizer->normalize($asset, 'standard', [])->willReturn(
            [
                'code' => 'ham',
                'localized' => true,
                'description' => 'Ham is better with jam!',
                'end_of_use' => '2041-01-01T00:00:00+0200',
                'categories' => [
                    'meat',
                ],
                'tags' => [
                    'cooking',
                ],
            ]
        );

        $asset->getCode()->willReturn('ham');
        $asset->getVariations()->willReturn([$variation]);
        $asset->getReferences()->willReturn($references);

        $variationNormalizer->normalize($variation, 'external_api', [])->willReturn([
            '_link' => [
                'download' => [
                    'href' => 'http://akeneo.ham.com/rest/v1/assets/ham/variation-files/ecommerce/en_US/download',
                ],
            ],
            'locale' => 'en_US',
            'channel' => 'ecommerce',
            'code' => 'relative/path/to/the/reference_image_variation.jpg',
        ]);

        $references->getIterator()->willReturn($referencesIterator);
        $referencesIterator->rewind()->shouldBeCalled();
        $referencesIterator->valid()->willReturn(true, false);
        $referencesIterator->current()->willReturn($reference);
        $referencesIterator->next()->shouldBeCalled();

        $referenceNormalizer->normalize($reference, 'external_api', [])->willReturn([
            '_link' => [
                'download' => [
                    'href' => 'http://akeneo.ham.com/rest/v1/assets/ham/reference-files/en_US/download',
                ],
            ],
            'locale' => 'en_US',
            'code' => 'relative/path/to/the/reference_image.jpg',
        ]);

        $variation->getAsset()->willReturn($asset);
        $reference->getAsset()->willReturn($asset);

        $router->generate(
            'pimee_api_asset_variation_get',
            [
                'code' => 'ham',
                'channelCode' => 'ecommerce',
                'localeCode' => 'en_US',
            ],
            UrlGeneratorInterface::ABSOLUTE_URL
        )->willReturn('http://akeneo.ham.com/rest/v1/assets/ham/variation-files/ecommerce/en_US');

        $router->generate(
            'pimee_api_asset_reference_get',
            [
                'code' => 'ham',
                'localeCode' => 'en_US',
            ],
            UrlGeneratorInterface::ABSOLUTE_URL
        )->willReturn('http://akeneo.ham.com/rest/v1/assets/ham/reference-files/en_US');

        $this->normalize($asset, 'external_api', [])->shouldReturn(
            [
                'code' => 'ham',
                'localized' => true,
                'description' => 'Ham is better with jam!',
                'end_of_use' => '2041-01-01T00:00:00+0200',
                'categories' => [
                    'meat',
                ],
                'tags' => [
                    'cooking',
                ],
                'variation_files' => [
                    [
                        '_link' => [
                            'download' => [
                                'href' => 'http://akeneo.ham.com/rest/v1/assets/ham/variation-files/ecommerce/en_US/download',
                            ],
                            'self' => [
                                'href' => 'http://akeneo.ham.com/rest/v1/assets/ham/variation-files/ecommerce/en_US',
                            ],
                        ],
                        'locale' => 'en_US',
                        'channel' => 'ecommerce',
                        'code' => 'relative/path/to/the/reference_image_variation.jpg',
                    ],
                ],
                'reference_files' => [
                    [
                        '_link' => [
                            'download' => [
                                'href' => 'http://akeneo.ham.com/rest/v1/assets/ham/reference-files/en_US/download',
                            ],
                            'self' => [
                                'href' => 'http://akeneo.ham.com/rest/v1/assets/ham/reference-files/en_US',
                            ],
                        ],
                        'locale' => 'en_US',
                        'code' => 'relative/path/to/the/reference_image.jpg',
                    ],
                ],
            ]
        );
    }

    function it_normalizes_an_asset_with_missing_variation_file(
        $standardAssetNormalizer,
        $variationNormalizer,
        $referenceNormalizer,
        $router,
        AssetInterface $asset,
        VariationInterface $variationA,
        VariationInterface $variationB,
        Collection $references,
        \Iterator $referencesIterator,
        ReferenceInterface $reference
    ) {
        $standardAssetNormalizer->normalize($asset, 'standard', [])->willReturn(
            [
                'code' => 'ham',
                'localized' => true,
                'description' => 'Ham is better with jam!',
                'end_of_use' => '2041-01-01T00:00:00+0200',
                'categories' => [
                    'meat',
                ],
                'tags' => [
                    'cooking',
                ],
            ]
        );

        $asset->getCode()->willReturn('ham');
        $asset->getVariations()->willReturn([$variationA, $variationB]);
        $asset->getReferences()->willReturn($references);

        $variationNormalizer->normalize($variationA, 'external_api', [])->willReturn([
            '_link' => [
                'download' => [
                    'href' => 'http://akeneo.ham.com/rest/v1/assets/ham/variation-files/ecommerce/en_US/download',
                ],
            ],
            'locale' => 'en_US',
            'channel' => 'ecommerce',
            'code' => 'relative/path/to/the/reference_image_variation.jpg',
        ]);

        $variationNormalizer->normalize($variationB, 'external_api', [])->willReturn([
            '_link' => [
                'download' => [
                    'href' => 'http://akeneo.ham.com/rest/v1/assets/ham/variation-files/ecommerce/en_US/download',
                ],
            ],
            'locale' => 'en_US',
            'channel' => 'tablet',
            'code' => null,
        ]);

        $references->getIterator()->willReturn($referencesIterator);
        $referencesIterator->rewind()->shouldBeCalled();
        $referencesIterator->valid()->willReturn(true, false);
        $referencesIterator->current()->willReturn($reference);
        $referencesIterator->next()->shouldBeCalled();

        $referenceNormalizer->normalize($reference, 'external_api', [])->willReturn([
            '_link' => [
                'download' => [
                    'href' => 'http://akeneo.ham.com/rest/v1/assets/ham/reference-files/en_US/download',
                ],
            ],
            'locale' => 'en_US',
            'code' => 'relative/path/to/the/reference_image.jpg',
        ]);

        $reference->getAsset()->willReturn($asset);
        $variationA->getAsset()->willReturn($asset);

        $router->generate(
            'pimee_api_asset_variation_get',
            [
                'code' => 'ham',
                'channelCode' => 'ecommerce',
                'localeCode' => 'en_US',
            ],
            UrlGeneratorInterface::ABSOLUTE_URL
        )->willReturn('http://akeneo.ham.com/rest/v1/assets/ham/variation-files/ecommerce/en_US');

        $router->generate(
            'pimee_api_asset_reference_get',
            [
                'code' => 'ham',
                'localeCode' => 'en_US',
            ],
            UrlGeneratorInterface::ABSOLUTE_URL
        )->willReturn('http://akeneo.ham.com/rest/v1/assets/ham/reference-files/en_US');

        $this->normalize($asset, 'external_api', [])->shouldReturn(
            [
                'code' => 'ham',
                'localized' => true,
                'description' => 'Ham is better with jam!',
                'end_of_use' => '2041-01-01T00:00:00+0200',
                'categories' => [
                    'meat',
                ],
                'tags' => [
                    'cooking',
                ],
                'variation_files' => [
                    [
                        '_link' => [
                            'download' => [
                                'href' => 'http://akeneo.ham.com/rest/v1/assets/ham/variation-files/ecommerce/en_US/download',
                            ],
                            'self' => [
                                'href' => 'http://akeneo.ham.com/rest/v1/assets/ham/variation-files/ecommerce/en_US',
                            ],
                        ],
                        'locale' => 'en_US',
                        'channel' => 'ecommerce',
                        'code' => 'relative/path/to/the/reference_image_variation.jpg',
                    ],
                ],
                'reference_files' => [
                    [
                        '_link' => [
                            'download' => [
                                'href' => 'http://akeneo.ham.com/rest/v1/assets/ham/reference-files/en_US/download',
                            ],
                            'self' => [
                                'href' => 'http://akeneo.ham.com/rest/v1/assets/ham/reference-files/en_US',
                            ],
                        ],
                        'locale' => 'en_US',
                        'code' => 'relative/path/to/the/reference_image.jpg',
                    ],
                ],
            ]
        );
    }

    function it_normalizes_an_asset_without_variation_files(
        $standardAssetNormalizer,
        $variationNormalizer,
        $referenceNormalizer,
        $router,
        AssetInterface $asset,
        VariationInterface $variationA,
        VariationInterface $variationB,
        Collection $references,
        \Iterator $referencesIterator,
        ReferenceInterface $reference
    ) {
        $standardAssetNormalizer->normalize($asset, 'standard', [])->willReturn(
            [
                'code' => 'ham',
                'localized' => true,
                'description' => 'Ham is better with jam!',
                'end_of_use' => '2041-01-01T00:00:00+0200',
                'categories' => [
                    'meat',
                ],
                'tags' => [
                    'cooking',
                ],
            ]
        );

        $asset->getCode()->willReturn('ham');
        $asset->getVariations()->willReturn([$variationA, $variationB]);
        $asset->getReferences()->willReturn($references);

        $variationNormalizer->normalize($variationA, 'external_api', [])->willReturn([
            '_link' => [
                'download' => [
                    'href' => 'http://akeneo.ham.com/rest/v1/assets/ham/variation-files/ecommerce/en_US/download',
                ],
            ],
            'locale' => 'en_US',
            'channel' => 'ecommerce',
            'code' => null,
        ]);

        $variationNormalizer->normalize($variationB, 'external_api', [])->willReturn([
            '_link' => [
                'download' => [
                    'href' => 'http://akeneo.ham.com/rest/v1/assets/ham/variation-files/ecommerce/en_US/download',
                ],
            ],
            'locale' => 'en_US',
            'channel' => 'tablet',
            'code' => null,
        ]);

        $references->getIterator()->willReturn($referencesIterator);
        $referencesIterator->rewind()->shouldBeCalled();
        $referencesIterator->valid()->willReturn(true, false);
        $referencesIterator->current()->willReturn($reference);
        $referencesIterator->next()->shouldBeCalled();

        $referenceNormalizer->normalize($reference, 'external_api', [])->willReturn([
            '_link' => [
                'download' => [
                    'href' => 'http://akeneo.ham.com/rest/v1/assets/ham/reference-files/en_US/download',
                ],
            ],
            'locale' => 'en_US',
            'code' => 'relative/path/to/the/reference_image.jpg',
        ]);

        $reference->getAsset()->willReturn($asset);

        $router->generate('pimee_api_asset_variation_get', Argument::any())->shouldNotBeCalled();

        $router->generate(
            'pimee_api_asset_reference_get',
            [
                'code' => 'ham',
                'localeCode' => 'en_US',
            ],
            UrlGeneratorInterface::ABSOLUTE_URL
        )->willReturn('http://akeneo.ham.com/rest/v1/assets/ham/reference-files/en_US');

        $this->normalize($asset, 'external_api', [])->shouldReturn(
            [
                'code' => 'ham',
                'localized' => true,
                'description' => 'Ham is better with jam!',
                'end_of_use' => '2041-01-01T00:00:00+0200',
                'categories' => [
                    'meat',
                ],
                'tags' => [
                    'cooking',
                ],
                'variation_files' => [],
                'reference_files' => [
                    [
                        '_link' => [
                            'download' => [
                                'href' => 'http://akeneo.ham.com/rest/v1/assets/ham/reference-files/en_US/download',
                            ],
                            'self' => [
                                'href' => 'http://akeneo.ham.com/rest/v1/assets/ham/reference-files/en_US',
                            ],
                        ],
                        'locale' => 'en_US',
                        'code' => 'relative/path/to/the/reference_image.jpg',
                    ],
                ],
            ]
        );
    }

    function it_normalizes_an_asset_without_reference_nor_variation_files(
        $standardAssetNormalizer,
        $variationNormalizer,
        $referenceNormalizer,
        $router,
        AssetInterface $asset,
        VariationInterface $variation,
        Collection $references,
        \Iterator $referencesIterator,
        ReferenceInterface $reference
    ) {
        $standardAssetNormalizer->normalize($asset, 'standard', [])->willReturn(
            [
                'code' => 'ham',
                'localized' => false,
                'description' => 'Ham is better with jam!',
                'end_of_use' => '2041-01-01T00:00:00+0200',
                'categories' => [
                    'meat',
                ],
                'tags' => [
                    'cooking',
                ],
            ]
        );

        $asset->getCode()->willReturn('ham');
        $asset->getVariations()->willReturn([$variation]);
        $asset->getReferences()->willReturn($references);

        $variationNormalizer->normalize($variation, 'external_api', [])->willReturn([
            '_link' => [
                'download' => [
                    'href' => 'http://akeneo.ham.com/rest/v1/assets/ham/variation-files/ecommerce/no-locale/download',
                ],
            ],
            'locale' => null,
            'channel' => 'ecommerce',
            'code' => null,
        ]);

        $references->getIterator()->willReturn($referencesIterator);
        $referencesIterator->rewind()->shouldBeCalled();
        $referencesIterator->valid()->willReturn(true, false);
        $referencesIterator->current()->willReturn($reference);
        $referencesIterator->next()->shouldBeCalled();

        $referenceNormalizer->normalize($reference, 'external_api', [])->willReturn([
            '_link' => [
                'download' => [
                    'href' => 'http://akeneo.ham.com/rest/v1/assets/ham/reference-files/no-locale/download',
                ],
            ],
            'locale' => null,
            'code' => null,
        ]);

        $router->generate(Argument::any())->shouldNotBeCalled();

        $this->normalize($asset, 'external_api', [])->shouldReturn(
            [
                'code' => 'ham',
                'localized' => false,
                'description' => 'Ham is better with jam!',
                'end_of_use' => '2041-01-01T00:00:00+0200',
                'categories' => [
                    'meat',
                ],
                'tags' => [
                    'cooking',
                ],
                'variation_files' => [],
                'reference_files' => [],
            ]
        );
    }
}
