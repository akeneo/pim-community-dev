<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi;

use Akeneo\Pim\Enrichment\Component\Product\Model\Projection\ProductCompleteness;
use Akeneo\Pim\Enrichment\Component\Product\Model\Projection\ProductCompletenessCollection;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\ProductCompletenessCollectionNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetAttributeLabelsInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetChannelLabelsInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductCompletenessCollectionNormalizerSpec extends ObjectBehavior
{
    function let(
        NormalizerInterface $normalizer,
        GetChannelLabelsInterface $getChannelLabels,
        GetAttributeLabelsInterface $getAttributeLabels
    )
    {
        $this->beConstructedWith(
            $normalizer,
            $getChannelLabels,
            $getAttributeLabels
        );
    }

    function it_is_a_product_completeness_collection_normalizer()
    {
        $this->shouldHaveType(ProductCompletenessCollectionNormalizer::class);
    }

    function it_normalizes_completenesses_and_indexes_them(
        NormalizerInterface $normalizer,
        GetChannelLabelsInterface $getChannelLabels,
        GetAttributeLabelsInterface $getAttributeLabels
    )
    {
        $completenessCollection = new ProductCompletenessCollection(
            42,
            [
                new ProductCompleteness('mobile', 'en_US', 2, ['name']),
                new ProductCompleteness('mobile', 'fr_FR', 2, ['sku']),
                new ProductCompleteness('print', 'en_US', 2, ['description']),
                new ProductCompleteness('print', 'fr_FR', 2, ['name']),
                new ProductCompleteness('1234567890', 'en_US', 2, ['name']),
                new ProductCompleteness('1234567890', 'fr_FR', 2, ['name']),
            ]
        );

        $getChannelLabels->getLabels(['mobile', 'print', '1234567890'])->willReturn([
            'mobile' => [
                'en_US' => 'Mobile',
                'fr_FR' => 'Mobile',
            ],
            'print' => [
                'en_US' => 'Print',
            ]
        ]);

        $getAttributeLabels->getLabels(['name', 'sku', 'description'])->willReturn([
            'sku' => [
                'en_US' => 'SKU',
                'fr_FR' => 'SKU',
            ],
            'name' => [
                'en_US' => 'Name'
            ]
        ]);

        $normalizer->normalize(
            Argument::type(ProductCompleteness::class),
            'internal_api'
        )->willReturn([])->shouldBeCalledTimes(6);

        $this
            ->normalize($completenessCollection, 'internal_api')
            ->shouldReturn(
                [
                    [
                        'channel' => 'mobile',
                        'labels' => [
                            'en_US' => 'Mobile',
                            'fr_FR' => 'Mobile',
                        ],
                        'stats' => [
                            'total' => 2,
                            'complete' => 0,
                            'average' => 50,
                        ],
                        'locales' => [
                            'en_US' => [
                                'completeness' => [],
                                'missing' => [
                                    [
                                        'code' => 'name',
                                        'labels' => [
                                            'en_US' => 'Name',
                                            'fr_FR' => '[name]',
                                        ],
                                    ],
                                ],
                                'label' => 'English (United States)',
                            ],
                            'fr_FR' => [
                                'completeness' => [],
                                'missing' => [
                                    [
                                        'code' => 'sku',
                                        'labels' => [
                                            'en_US' => 'SKU',
                                            'fr_FR' => 'SKU',
                                        ],
                                    ],
                                ],
                                'label' => 'French (France)',
                            ],
                        ],
                    ],
                    [
                        'channel' => 'print',
                        'labels' => [
                            'en_US' => 'Print',
                            'fr_FR' => '[print]',
                        ],
                        'stats' => [
                            'total' => 2,
                            'complete' => 0,
                            'average' => 50,
                        ],
                        'locales' => [
                            'en_US' => [
                                'completeness' => [],
                                'missing' => [
                                    [
                                        'code' => 'description',
                                        'labels' => [
                                            'en_US' => '[description]',
                                            'fr_FR' => '[description]',
                                        ],
                                    ],
                                ],
                                'label' => 'English (United States)',
                            ],
                            'fr_FR' => [
                                'completeness' => [],
                                'missing' => [
                                    [
                                        'code' => 'name',
                                        'labels' => [
                                            'en_US' => 'Name',
                                            'fr_FR' => '[name]',
                                        ],
                                    ],
                                ],
                                'label' => 'French (France)',
                            ],
                        ],
                    ],
                    [
                        'channel' => '1234567890',
                        'labels' => [
                            'en_US' => '[1234567890]',
                            'fr_FR' => '[1234567890]',
                        ],
                        'stats' => [
                            'total' => 2,
                            'complete' => 0,
                            'average' => 50,
                        ],
                        'locales' => [
                            'en_US' => [
                                'completeness' => [],
                                'missing' => [
                                    [
                                        'code' => 'name',
                                        'labels' => [
                                            'en_US' => 'Name',
                                            'fr_FR' => '[name]',
                                        ],
                                    ],
                                ],
                                'label' => 'English (United States)',
                            ],
                            'fr_FR' => [
                                'completeness' => [],
                                'missing' => [
                                    [
                                        'code' => 'name',
                                        'labels' => [
                                            'en_US' => 'Name',
                                            'fr_FR' => '[name]',
                                        ],
                                    ],
                                ],
                                'label' => 'French (France)',
                            ],
                        ],
                    ],
                ]
            );
    }
}
