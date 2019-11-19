<?php

declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation;

use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\OperationCollection;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Source;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Target;
use PhpSpec\ObjectBehavior;

class TransformationSpec extends ObjectBehavior
{
    function it_creates_a_transformation(Source $source, Target $target)
    {
        $source->equals($target)->willReturn(false);

        $this->beConstructedThrough('create', [
            $source,
            $target,
            OperationCollection::create([]),
        ]);
        $this->getWrappedObject();
    }

    function it_throws_an_exception_if_target_is_equal_to_source(Source $source, Target $target)
    {
        $source->equals($target)->willReturn(true);

        $this->beConstructedThrough('create', [
            $source,
            $target,
            OperationCollection::create([]),
        ]);

        $this->shouldThrow(new \InvalidArgumentException('A transformation can not have the same source and target'))->duringInstantiation();
    }

    function it_normalizes_a_transformation(
        Source $source,
        Target $target,
        Operation $operation1,
        Operation $operation2
    ) {
        $source->equals($target)->willReturn(false);

        $this->beConstructedThrough('create', [
            $source,
            $target,
            OperationCollection::create([$operation1->getWrappedObject(), $operation2->getWrappedObject()])
        ]);
        $normalizedSource = ['key' => 'normalized source'];
        $normalizedTarget = ['key' => 'normalized target'];
        $normalizedOperation1 = ['key' => 'normalized operation 1'];
        $normalizedOperation2 = ['key' => 'normalized operation 2'];

        $source->normalize()->willReturn($normalizedSource);
        $target->normalize()->willReturn($normalizedTarget);
        $operation1->normalize()->willReturn($normalizedOperation1);
        $operation2->normalize()->willReturn($normalizedOperation2);

        $this->normalize()->shouldReturn([
            'source' => $normalizedSource,
            'target' => $normalizedTarget,
            'operations' => [
                $normalizedOperation1,
                $normalizedOperation2
            ]
        ]);
    }

    function it_can_be_created_from_normalized_format()
    {
        $normalizedTransformation = [
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
        ];

        $this->beConstructedThrough('createFromNormalized', [$normalizedTransformation]);
        $this->getSource()->getAttributeIdentifier()->stringValue()->shouldReturn('my-source-attribute');
        $this->getSource()->getChannelReference()->normalize()->shouldReturn(null);
        $this->getSource()->getLocaleReference()->normalize()->shouldReturn(null);

        $this->getTarget()->getAttributeIdentifier()->stringValue()->shouldReturn('my-localizable-target');
        $this->getTarget()->getChannelReference()->normalize()->shouldReturn(null);
        $this->getTarget()->getLocaleReference()->normalize()->shouldReturn('en_US');
    }
}
