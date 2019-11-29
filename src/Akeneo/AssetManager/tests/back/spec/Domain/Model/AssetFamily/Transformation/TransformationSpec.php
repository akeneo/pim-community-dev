<?php

declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation;

use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation\ResizeOperation;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation\ThumbnailOperation;
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
            'prefix',
            'suffix',
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
            'prefix',
            'suffix',
        ]);

        $this->shouldThrow(new \InvalidArgumentException('A transformation can not have the same source and target'))->duringInstantiation();
    }

    function it_normalizes_a_transformation(Source $source, Target $target)
    {
        $operation1 = ThumbnailOperation::create(['width' => 100, 'height' => 80]);
        $operation2 = ResizeOperation::create(['width' => 100, 'height' => 80]);

        $source->equals($target)->willReturn(false);

        $this->beConstructedThrough('create', [
            $source,
            $target,
            OperationCollection::create([$operation1, $operation2]),
            'prefix',
            'suffix',
        ]);
        $normalizedSource = ['key' => 'normalized source'];
        $normalizedTarget = ['key' => 'normalized target'];

        $source->normalize()->willReturn($normalizedSource);
        $target->normalize()->willReturn($normalizedTarget);

        $this->normalize()->shouldReturn([
            'source' => $normalizedSource,
            'target' => $normalizedTarget,
            'operations' => [
                $operation1->normalize(),
                $operation2->normalize()
            ],
            'filename_prefix' => 'prefix',
            'filename_suffix' => 'suffix',
        ]);
    }

    function it_can_construct_transformation_with_only_prefix(Source $source, Target $target)
    {
        $source->equals($target)->willReturn(false);

        $this->beConstructedThrough('create', [
            $source,
            $target,
            OperationCollection::create([]),
            'prefix',
            '',
        ]);
        $this->getWrappedObject();
    }

    function it_can_construct_transformation_with_only_suffix(Source $source, Target $target)
    {
        $source->equals($target)->willReturn(false);

        $this->beConstructedThrough('create', [
            $source,
            $target,
            OperationCollection::create([]),
            '',
            'suffix',
        ]);
        $this->getWrappedObject();
    }

    function it_can_not_construct_transformation_without_prefix_and_suffix(Source $source, Target $target)
    {
        $source->equals($target)->willReturn(false);

        $this->beConstructedThrough('create', [
            $source,
            $target,
            OperationCollection::create([]),
            '   ',
            '   ',
        ]);
        $this->shouldThrow(new \InvalidArgumentException('A transformation must have at least a filename prefix or a filename suffix'))
            ->duringInstantiation();
    }
}
