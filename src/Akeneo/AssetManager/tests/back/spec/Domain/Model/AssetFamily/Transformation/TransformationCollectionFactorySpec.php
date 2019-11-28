<?php

namespace spec\Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation;

use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation\ColorspaceOperation;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation\ThumbnailOperation;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\OperationFactory;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\TransformationCollectionFactory;
use Akeneo\AssetManager\Domain\Model\AssetFamily\TransformationCollection;
use PhpSpec\ObjectBehavior;

class TransformationCollectionFactorySpec extends ObjectBehavior
{
    function let()
    {
        $factory = new OperationFactory([
            ThumbnailOperation::class,
            ColorspaceOperation::class
        ]);
        $this->beConstructedWith($factory);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(TransformationCollectionFactory::class);
    }

    function it_creates_a_transformation()
    {
        $transformationCollection = $this->fromNormalized(
            [
                [
                    'source' => [
                        'attribute' => 'source',
                        'channel' => null,
                        'locale' => null,
                    ],
                    'target' => [
                        'attribute' => 'target',
                        'channel' => null,
                        'locale' => null,
                    ],
                    'operations' => [
                        [
                            'type' => 'thumbnail',
                            'parameters' => [
                                'width' => 200,
                            ],
                        ],
                        [
                            'type' => 'colorspace',
                            'parameters' => [
                                'colorspace' => 'grey',
                            ]
                        ]
                    ],
                    'filename_prefix' => 'pre',
                    'filename_suffix' => '_2',
                ],
                [
                    'source' => [
                        'attribute' => 'source',
                        'channel' => null,
                        'locale' => null,
                    ],
                    'target' => [
                        'attribute' => 'target_2',
                        'channel' => null,
                        'locale' => null,
                    ],
                    'operations' => [
                        [
                            'type' => 'thumbnail',
                            'parameters' => [
                                'width' => 200,
                                'height' => 200,
                            ],
                        ],
                    ],
                    'filename_prefix' => 'pre',
                    'filename_suffix' => '_2',
                ],
            ]
        );

        $transformationCollection->shouldBeAnInstanceOf(TransformationCollection::class);
    }

    function it_throws_an_exception_if_source_is_missing()
    {
        $transformationCollection = [
            [
                'target' => [
                    'attribute' => 'target',
                    'channel' => null,
                    'locale' => null,
                ],
                'operations' => [],
                'filename_prefix' => '',
                'filename_suffix' => '_2',
            ],
        ];

        $this->shouldThrow(\InvalidArgumentException::class)->during('fromNormalized', [$transformationCollection]);
    }

    function it_throws_an_exception_if_target_is_missing()
    {
        $transformationCollection = [
            [
                'source' => [
                    'attribute' => 'source',
                    'channel' => null,
                    'locale' => null,
                ],
                'operations' => [],
                'filename_prefix' => '',
                'filename_suffix' => '_2',
            ],
        ];

        $this->shouldThrow(\InvalidArgumentException::class)->during('fromNormalized', [$transformationCollection]);
    }

    function it_throws_an_exception_if_operations_is_missing()
    {
        $transformationCollection = [
            [
                'source' => [
                    'attribute' => 'source',
                    'channel' => null,
                    'locale' => null,
                ],
                'target' => [
                    'attribute' => 'target',
                    'channel' => null,
                    'locale' => null,
                ],
                'filename_prefix' => '',
                'filename_suffix' => '_2',
            ],
        ];

        $this->shouldThrow(\InvalidArgumentException::class)->during('fromNormalized', [$transformationCollection]);
    }

    function it_throws_an_exception_if_source_is_not_an_array()
    {
        $transformationCollection = [
            [
                'source' => 'foo',
                'target' => [
                    'attribute' => 'target',
                    'channel' => null,
                    'locale' => null,
                ],
                'operations' => [],
                'filename_prefix' => '',
                'filename_suffix' => '_2',
            ],
        ];

        $this->shouldThrow(\InvalidArgumentException::class)->during('fromNormalized', [$transformationCollection]);
    }

    function it_throws_an_exception_if_filename_prefix_is_missing()
    {
        $transformationCollection = [
            [
                'source' => [
                    'attribute' => 'source',
                    'channel' => null,
                    'locale' => null,
                ],
                'target' => [
                    'attribute' => 'target',
                    'channel' => null,
                    'locale' => null,
                ],
                'filename_suffix' => '_2',
            ],
        ];

        $this->shouldThrow(\InvalidArgumentException::class)->during('fromNormalized', [$transformationCollection]);
    }


    function it_throws_an_exception_if_filename_suffix_is_missing()
    {
        $transformationCollection = [
            [
                'source' => [
                    'attribute' => 'source',
                    'channel' => null,
                    'locale' => null,
                ],
                'target' => [
                    'attribute' => 'target',
                    'channel' => null,
                    'locale' => null,
                ],
                'operations' => [],
                'filename_prefix' => 'prefix_',
            ],
        ];

        $this->shouldThrow(\InvalidArgumentException::class)->during('fromNormalized', [$transformationCollection]);
    }

}
