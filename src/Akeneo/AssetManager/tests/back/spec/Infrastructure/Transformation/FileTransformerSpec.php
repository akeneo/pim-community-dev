<?php

declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Infrastructure\Transformation;

use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\OperationCollection;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Transformation;
use Akeneo\AssetManager\Infrastructure\Transformation\FileTransformer;
use Akeneo\AssetManager\Infrastructure\Transformation\Operation\OperationApplier;
use Akeneo\AssetManager\Infrastructure\Transformation\Operation\OperationApplierRegistry;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;

class FileTransformerSpec extends ObjectBehavior
{
    function let(
        OperationApplier $scaleOperationApplier,
        OperationApplier $thumbnailOperationApplier,
        Filesystem $filesystem
    ) {
        // registry with 'scale' and 'thumbnail' operation appliers
        $scaleOperationApplier->supports(Argument::type(Operation\ScaleOperation::class))->willReturn(true);
        $scaleOperationApplier->supports(Argument::type(Operation::class))->willReturn(false);
        $thumbnailOperationApplier->supports(Argument::type(Operation\ThumbnailOperation::class))->willReturn(true);
        $thumbnailOperationApplier->supports(Argument::type(Operation::class))->willReturn(false);
        $applierRegistry = new OperationApplierRegistry(
            [$scaleOperationApplier->getWrappedObject(), $thumbnailOperationApplier->getWrappedObject()]
        );

        $this->beConstructedWith($applierRegistry, $filesystem);
    }

    function it_is_a_file_transformer()
    {
        $this->shouldHaveType(FileTransformer::class);
    }

    function it_throws_an_exception_if_no_applier_was_found(
        Operation $unknownOperation,
        Transformation $transformation
    ) {
        $transformation->getOperationCollection()->willReturn(
            OperationCollection::create([$unknownOperation->getWrappedObject()])
        );

        $this->shouldThrow(\RuntimeException::class)->during(
            'transform',
            [
                new File('/my/file/path', false),
                $transformation
            ]
        );
    }

    function it_applies_a_transformation_to_a_file(
        Filesystem $filesystem,
        OperationApplier $scaleOperationApplier,
        OperationApplier $thumbnailOperationApplier,
        Transformation $transformation
    ) {
        $scale = Operation\ScaleOperation::create(['ratio' => 50]);
        $thumbnail = Operation\ThumbnailOperation::create(['height' => 100]);
        $transformation->getOperationCollection()->willReturn(OperationCollection::create([$scale, $thumbnail]));
        $transformation->getFilenamePrefix()->willReturn('thumb_');
        $transformation->getFilenameSuffix()->willReturn('-42');

        $sourceFile = new File('/my/file/jambon.png', false);
        $filesystem->copy('/my/file/jambon.png', '/my/file/thumb_jambon-42.png')->shouldBeCalled();

        $transformedFile = new File('/my/file/thumb_jambon-42.png', false);

        $scaleOperationApplier->apply($transformedFile, $scale)->shouldBeCalled()->willReturn($transformedFile);
        $thumbnailOperationApplier->apply($transformedFile, $thumbnail)->shouldBeCalled()->willReturn($transformedFile);

        $this->transform($sourceFile, $transformation)->shouldReturn($transformedFile);
    }
}
