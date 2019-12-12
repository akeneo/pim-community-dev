<?php

declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation;

use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation\ColorspaceOperation;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation\ResizeOperation;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation\ThumbnailOperation;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\OperationCollection;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Source;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Target;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Transformation;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\TransformationCode;
use Akeneo\AssetManager\Domain\Model\AssetFamily\TransformationCollection;
use Akeneo\AssetManager\Domain\Query\ClockInterface;
use PhpSpec\ObjectBehavior;

class TransformationSpec extends ObjectBehavior
{
    function it_creates_a_transformation(Source $source, Target $target)
    {
        $source->equals($target)->willReturn(false);

        $this->beConstructedThrough('create', [
            TransformationCode::fromString('code'),
            $source,
            $target,
            OperationCollection::create([]),
            'prefix',
            'suffix',
            new \DateTimeImmutable(),
        ]);
        $this->getWrappedObject();
    }

    function it_throws_an_exception_if_target_is_equal_to_source(Source $source, Target $target)
    {
        $source->equals($target)->willReturn(true);

        $this->beConstructedThrough('create', [
            TransformationCode::fromString('code'),
            $source,
            $target,
            OperationCollection::create([]),
            'prefix',
            'suffix',
            new \DateTimeImmutable(),
        ]);

        $this->shouldThrow(new \InvalidArgumentException('A transformation can not have the same source and target'))->duringInstantiation();
    }

    function it_normalizes_a_transformation(Source $source, Target $target)
    {
        $operation1 = ThumbnailOperation::create(['width' => 100, 'height' => 80]);
        $operation2 = ResizeOperation::create(['width' => 100, 'height' => 80]);

        $source->equals($target)->willReturn(false);

        $updatedAt = new \DateTimeImmutable('1990-01-01');
        $this->beConstructedThrough('create', [
            TransformationCode::fromString('code'),
            $source,
            $target,
            OperationCollection::create([$operation1, $operation2]),
            'prefix',
            'suffix',
            $updatedAt,
        ]);
        $normalizedSource = ['key' => 'normalized source'];
        $normalizedTarget = ['key' => 'normalized target'];

        $source->normalize()->willReturn($normalizedSource);
        $target->normalize()->willReturn($normalizedTarget);

        $this->normalize()->shouldReturn([
            'code' => 'code',
            'source' => $normalizedSource,
            'target' => $normalizedTarget,
            'operations' => [
                $operation1->normalize(),
                $operation2->normalize()
            ],
            'filename_prefix' => 'prefix',
            'filename_suffix' => 'suffix',
            'updated_at' => $updatedAt->format(\DateTimeInterface::ISO8601),
        ]);
    }

    function it_does_not_return_null_values_in_normalization(Source $source, Target $target)
    {
        $operation1 = ThumbnailOperation::create(['width' => 100, 'height' => 80]);
        $operation2 = ResizeOperation::create(['width' => 100, 'height' => 80]);

        $source->equals($target)->willReturn(false);

        $updatedAt = new \DateTimeImmutable();
        $this->beConstructedThrough('create', [
            TransformationCode::fromString('code'),
            $source,
            $target,
            OperationCollection::create([$operation1, $operation2]),
            null,
            ' ',
            $updatedAt
        ]);
        $normalizedSource = ['key' => 'normalized source'];
        $normalizedTarget = ['key' => 'normalized target'];

        $source->normalize()->willReturn($normalizedSource);
        $target->normalize()->willReturn($normalizedTarget);

        $this->normalize()->shouldReturn([
            'code' => 'code',
            'source' => $normalizedSource,
            'target' => $normalizedTarget,
            'operations' => [
                $operation1->normalize(),
                $operation2->normalize()
            ],
            'filename_suffix' => ' ',
            'updated_at' => $updatedAt->format(\DateTimeInterface::ISO8601),
        ]);
    }

    function it_can_construct_transformation_with_only_prefix(Source $source, Target $target)
    {
        $source->equals($target)->willReturn(false);

        $this->beConstructedThrough('create', [
            TransformationCode::fromString('code'),
            $source,
            $target,
            OperationCollection::create([]),
            'prefix',
            null,
            new \DateTimeImmutable(),
        ]);
        $this->getWrappedObject();
    }

    function it_can_construct_transformation_with_only_suffix(Source $source, Target $target)
    {
        $source->equals($target)->willReturn(false);

        $this->beConstructedThrough('create', [
            TransformationCode::fromString('code'),
            $source,
            $target,
            OperationCollection::create([]),
            null,
            'suffix',
            new \DateTimeImmutable(),
        ]);
        $this->getWrappedObject();
    }

    function it_can_construct_transformation_with_spaces_in_prefix_and_suffix(Source $source, Target $target)
    {
        $source->equals($target)->willReturn(false);

        $this->beConstructedThrough('create', [
            TransformationCode::fromString('code'),
            $source,
            $target,
            OperationCollection::create([]),
            '   ',
            '   ',
            new \DateTimeImmutable(),
        ]);
        $this->getWrappedObject();
    }

    function it_can_not_construct_transformation_without_prefix_and_suffix(Source $source, Target $target)
    {
        $source->equals($target)->willReturn(false);

        $this->beConstructedThrough('create', [
            TransformationCode::fromString('code'),
            $source,
            $target,
            OperationCollection::create([]),
            null,
            null,
            new \DateTimeImmutable(),
        ]);
        $this->shouldThrow(new \InvalidArgumentException('A transformation must have at least a filename prefix or a filename suffix'))
            ->duringInstantiation();
    }

    function it_can_not_construct_transformation_with_empty_prefix_and_suffix(Source $source, Target $target)
    {
        $source->equals($target)->willReturn(false);

        $this->beConstructedThrough('create', [
            TransformationCode::fromString('code'),
            $source,
            $target,
            OperationCollection::create([]),
            '',
            '',
            new \DateTimeImmutable(),
        ]);
        $this->shouldThrow(new \InvalidArgumentException('A transformation must have at least a filename prefix or a filename suffix'))
            ->duringInstantiation();
    }

    function it_returns_target_with_prefix_and_suffix(Source $source, Target $target)
    {
        $source->equals($target)->willReturn(false);
        $this->beConstructedThrough('create', [
            TransformationCode::fromString('code'),
            $source,
            $target,
            OperationCollection::create([]),
            'prefix',
            'suffix',
            new \DateTimeImmutable(),
        ]);
        $this->getTargetFilename('jambon.png')->shouldReturn('prefixjambonsuffix.png');
    }

    function it_returns_target_with_prefix(Source $source, Target $target)
    {
        $source->equals($target)->willReturn(false);
        $this->beConstructedThrough('create', [
            TransformationCode::fromString('code'),
            $source,
            $target,
            OperationCollection::create([]),
            'prefix',
            null,
            new \DateTimeImmutable(),
        ]);
        $this->getTargetFilename('jambon.png')->shouldReturn('prefixjambon.png');
    }

    function it_returns_target_with_suffix(Source $source, Target $target)
    {
        $source->equals($target)->willReturn(false);
        $this->beConstructedThrough('create', [
            TransformationCode::fromString('code'),
            $source,
            $target,
            OperationCollection::create([]),
            null,
            'suffix',
            new \DateTimeImmutable(),
        ]);
        $this->getTargetFilename('jambon.png')->shouldReturn('jambonsuffix.png');
    }

    function it_returns_target_without_extension(Source $source, Target $target)
    {
        $source->equals($target)->willReturn(false);
        $this->beConstructedThrough('create', [
            TransformationCode::fromString('code'),
            $source,
            $target,
            OperationCollection::create([]),
            'prefix',
            'suffix',
            new \DateTimeImmutable(),
        ]);
        $this->getTargetFilename('jambon')->shouldReturn('prefixjambonsuffix');
    }

    function it_is_equal_to_another_transformation(
        Source $source1,
        Target $target1,
        OperationCollection $operationCollection1,
        Transformation $otherTransformation,
        Source $source2,
        Target $target2,
        OperationCollection $operationCollection2
    ) {
        $source1->equals($target1)->willReturn(false);
        $this->beConstructedThrough('create', [
            TransformationCode::fromString('code'),
            $source1,
            $target1,
            $operationCollection1,
            'pre',
            '',
            new \DateTimeImmutable(),
        ]);

        $otherTransformation->getCode()->willReturn(TransformationCode::fromString('code'));
        $otherTransformation->getSource()->willReturn($source2);
        $otherTransformation->getTarget()->willReturn($target2);
        $otherTransformation->getOperationCollection()->willReturn($operationCollection2);
        $otherTransformation->getFilenamePrefix()->willReturn('pre');
        $otherTransformation->getFilenameSuffix()->willReturn('');
        $otherTransformation->getUpdatedAt()->willReturn(new \DateTimeImmutable('2010-01-01'));
        $source1->equals($source2)->willReturn(true);
        $target1->equals($target2)->willReturn(true);
        $operationCollection1->equals($operationCollection2)->willReturn(true);

        $this->equals($otherTransformation)->shouldReturn(true);
    }

    function it_is_not_equal_to_another_transformation_if_target_is_not_equal(
        Source $source1,
        Target $target1,
        OperationCollection $operationCollection1,
        Transformation $otherTransformation,
        Source $source2,
        Target $target2,
        OperationCollection $operationCollection2
    ) {
        $source1->equals($target1)->willReturn(false);
        $this->beConstructedThrough('create', [
            TransformationCode::fromString('code'),
            $source1,
            $target1,
            $operationCollection1,
            'pre',
            '',
            new \DateTimeImmutable(),
        ]);

        $otherTransformation->getCode()->willReturn(TransformationCode::fromString('code'));
        $otherTransformation->getSource()->willReturn($source2);
        $otherTransformation->getTarget()->willReturn($target2);
        $otherTransformation->getOperationCollection()->willReturn($operationCollection2);
        $otherTransformation->getFilenamePrefix()->willReturn('pre');
        $otherTransformation->getFilenameSuffix()->willReturn('');
        $otherTransformation->getUpdatedAt()->willReturn(new \DateTimeImmutable('2010-01-01'));
        $source1->equals($source2)->willReturn(true);
        $target1->equals($target2)->willReturn(false);
        $operationCollection1->equals($operationCollection2)->willReturn(true);

        $this->equals($otherTransformation)->shouldReturn(false);
    }

    function it_is_not_equal_to_another_transformation_if_filename_is_not_equal(
        Source $source1,
        Target $target1,
        OperationCollection $operationCollection1,
        Transformation $otherTransformation,
        Source $source2,
        Target $target2,
        OperationCollection $operationCollection2
    ) {
        $source1->equals($target1)->willReturn(false);
        $this->beConstructedThrough('create', [
            TransformationCode::fromString('code'),
            $source1,
            $target1,
            $operationCollection1,
            'pre',
            '',
            new \DateTimeImmutable(),
        ]);

        $otherTransformation->getCode()->willReturn(TransformationCode::fromString('code'));
        $otherTransformation->getSource()->willReturn($source2);
        $otherTransformation->getTarget()->willReturn($target2);
        $otherTransformation->getOperationCollection()->willReturn($operationCollection2);
        $otherTransformation->getFilenamePrefix()->willReturn('pre');
        $otherTransformation->getFilenameSuffix()->willReturn('suffix');
        $otherTransformation->getUpdatedAt()->willReturn(new \DateTimeImmutable('2010-01-01'));
        $source1->equals($source2)->willReturn(true);
        $target1->equals($target2)->willReturn(true);
        $operationCollection1->equals($operationCollection2)->willReturn(true);

        $this->equals($otherTransformation)->shouldReturn(false);
    }

    function it_is_not_equal_to_another_transformation_if_operation_collection_is_not_equal(
        Source $source1,
        Target $target1,
        OperationCollection $operationCollection1,
        Transformation $otherTransformation,
        Source $source2,
        Target $target2,
        OperationCollection $operationCollection2
    ) {
        $source1->equals($target1)->willReturn(false);
        $this->beConstructedThrough('create', [
            TransformationCode::fromString('code'),
            $source1,
            $target1,
            $operationCollection1,
            'pre',
            '',
            new \DateTimeImmutable(),
        ]);

        $otherTransformation->getCode()->willReturn(TransformationCode::fromString('code'));
        $otherTransformation->getSource()->willReturn($source2);
        $otherTransformation->getTarget()->willReturn($target2);
        $otherTransformation->getOperationCollection()->willReturn($operationCollection2);
        $otherTransformation->getFilenamePrefix()->willReturn('pre');
        $otherTransformation->getFilenameSuffix()->willReturn('');
        $otherTransformation->getUpdatedAt()->willReturn(new \DateTimeImmutable('2010-01-01'));
        $source1->equals($source2)->willReturn(true);
        $target1->equals($target2)->willReturn(true);
        $operationCollection1->equals($operationCollection2)->willReturn(false);

        $this->equals($otherTransformation)->shouldReturn(false);
    }
}
