<?php

namespace spec\Akeneo\AssetManager\Domain\Model\AssetFamily;

use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Source;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Target;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Transformation;
use PhpSpec\ObjectBehavior;

class TransformationCollectionSpec extends ObjectBehavior
{
    function it_creates_a_transformation_collection(
        Transformation $transformation,
        Target $target,
        Source $source
    ) {
        $transformation->getTarget()->willReturn($target);
        $transformation->getSource()->willReturn($source);
        $target->equals($source)->willReturn(false);

        $this->beConstructedThrough('create', [[$transformation]]);
        $this->getWrappedObject();
    }

    function it_throws_an_exception_when_a_collection_item_is_not_a_transformation(
        Transformation $transformation1,
        Transformation $transformation2
    ) {
        $this->beConstructedThrough('create', [
            [
                $transformation1,
                new \stdClass(),
                $transformation2,
            ],
        ]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_throws_an_exception_when_2_transformations_have_the_same_target(
        Transformation $transformation1,
        Transformation $transformation2,
        Target $target1,
        Target $target2
    ) {
        $transformation1->getTarget()->willReturn($target1);
        $transformation2->getTarget()->willReturn($target2);

        $target1->equals($target2)->willReturn(true);

        $this->beConstructedThrough('create', [
            [
                $transformation1,
                $transformation2
            ]
        ]);
        $this->shouldThrow(new \InvalidArgumentException('You can not define 2 transformation with the same target'))->duringInstantiation();
    }

    function it_throws_an_exception_when_a_source_is_a_target_of_another_transformation(
        Transformation $transformation1,
        Transformation $transformation2,
        Target $target1,
        Target $target2,
        Source $source1,
        Source $source2
    ) {
        $transformation1->getTarget()->willReturn($target1);
        $transformation2->getTarget()->willReturn($target2);
        $transformation1->getSource()->willReturn($source1);
        $transformation2->getSource()->willReturn($source2);

        $target1->equals($source2)->willReturn(true);
        $target1->equals($target2)->willReturn(false);

        $this->beConstructedThrough('create', [
            [
                $transformation1,
                $transformation2
            ]
        ]);
        $this->shouldThrow(new \InvalidArgumentException('You can not define a transformation having a source as a target of another transformation'))->duringInstantiation();
    }

    function it_normalizes_a_transformation_collection(
        Transformation $transformation1,
        Transformation $transformation2,
        Target $target1,
        Target $target2,
        Source $source1,
        Source $source2
    ) {
        $this->beConstructedThrough('create', [[ $transformation1, $transformation2 ]]);

        $transformation1->getTarget()->willReturn($target1);
        $transformation2->getTarget()->willReturn($target2);
        $transformation1->getSource()->willReturn($source1);
        $transformation2->getSource()->willReturn($source2);
        $target1->equals($target2)->willReturn(false);
        $target1->equals($source2)->willReturn(false);
        $target2->equals($source1)->willReturn(false);

        $normalizedTransformation1 = ['key' => 'normalized transformation 1'];
        $normalizedTransformation2 = ['key' => 'normalized transformation 2'];

        $transformation1->normalize()->willReturn($normalizedTransformation1);
        $transformation2->normalize()->willReturn($normalizedTransformation2);

        $this->normalize()->shouldReturn([
            $normalizedTransformation1,
            $normalizedTransformation2,
        ]);
    }

    function it_can_be_instantiated_from_normalized_values()
    {
        $normalizedTransformations = [
            [
                'source' => [
                    'attribute' => 'my-source-attribute',
                    'channel' => null,
                    'locale' => null,
                ],
                'target' => [
                    'attribute' => 'my-localizable-target',
                    'channel' => null,
                    'locale' => 'en_US',
                ],
                'operations' => [
                    [
                        'name' => 'resize',
                        'parameters' => [
                            'width' => 200,
                            'height' => 160,
                        ],
                    ],
                    [
                        'scale' => 'resize',
                        'parameters' => [
                            'ratio' => '90%',
                        ],
                    ],
                ],
            ],
            [
                'source' => [
                    'attribute' => 'my-source-attribute',
                    'channel' => null,
                    'locale' => null,
                ],
                'target' => [
                    'attribute' => 'my-localizable-target',
                    'channel' => null,
                    'locale' => 'fr_FR',
                ],
                'operations' => [
                    [
                        'name' => 'resize',
                        'parameters' => [
                            'width' => 200,
                            'height' => 160,
                        ],
                    ],
                ],
            ],
        ];

        $this->beConstructedThrough('createFromNormalized', [$normalizedTransformations]);
        $this->shouldHaveCount(2);
    }
}
