<?php

declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Infrastructure\Transformation;

use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\OperationCollection;
use Akeneo\AssetManager\Infrastructure\Transformation\FileTransformer;
use Akeneo\AssetManager\Infrastructure\Transformation\Operation\OperationApplier;
use Akeneo\AssetManager\Infrastructure\Transformation\Operation\OperationApplierRegistry;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\File\File;

class FileTransformerSpec extends ObjectBehavior
{
    function let(
        OperationApplier $scaleOperationApplier,
        OperationApplier $thumbnailOperationApplier
    ) {
        // registry with 'scale' and 'thumbnail' operation appliers
        $scaleOperationApplier->supports(Argument::type(Operation\ScaleOperation::class))->willReturn(true);
        $scaleOperationApplier->supports(Argument::type(Operation::class))->willReturn(false);
        $thumbnailOperationApplier->supports(Argument::type(Operation\ThumbnailOperation::class))->willReturn(true);
        $thumbnailOperationApplier->supports(Argument::type(Operation::class))->willReturn(false);
        $applierRegistry = new OperationApplierRegistry(
            [$scaleOperationApplier->getWrappedObject(), $thumbnailOperationApplier->getWrappedObject()]
        );

        $this->beConstructedWith($applierRegistry);
    }

    function it_is_a_file_transformer()
    {
        $this->shouldHaveType(FileTransformer::class);
    }

    function it_throws_an_exception_if_no_applier_was_found(Operation $unknownOperation)
    {
        $this->shouldThrow(\RuntimeException::class)->during(
            'transform',
            [
                new File('/my/file/path', false),
                OperationCollection::create([$unknownOperation->getWrappedObject()])
            ]
        );
    }

    function it_applies_operations_to_a_file(
        OperationApplier $scaleOperationApplier,
        OperationApplier $thumbnailOperationApplier
    ) {
        $file = new File('/my/file/path', false);
        $scale = Operation\ScaleOperation::create(
            [
                'ratio' => 50,
            ]
        );
        $thumbnail = Operation\ThumbnailOperation::create(['height' => 100]);

        $operations = OperationCollection::create([$scale, $thumbnail]);

        $scaleOperationApplier->apply($file, $scale)->shouldBeCalled()->willReturn($file);
        $thumbnailOperationApplier->apply($file, $thumbnail)->shouldBeCalled()->willReturn($file);

        $this->transform($file, $operations)->shouldReturn($file);
    }
}
