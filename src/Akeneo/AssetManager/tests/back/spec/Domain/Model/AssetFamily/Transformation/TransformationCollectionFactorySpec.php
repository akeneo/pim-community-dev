<?php

namespace spec\Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation;

use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\TransformationCollectionFactory;
use Akeneo\AssetManager\Domain\Model\AssetFamily\TransformationCollection;
use PhpSpec\ObjectBehavior;

class TransformationCollectionFactorySpec extends ObjectBehavior
{
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
                    'operations' => [],
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
                    'operations' => [],
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
            ],
        ];

        $this->shouldThrow(\InvalidArgumentException::class)->during('fromNormalized', [$transformationCollection]);
    }
}
