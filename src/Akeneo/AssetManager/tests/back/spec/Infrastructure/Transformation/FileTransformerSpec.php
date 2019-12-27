<?php

declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Infrastructure\Transformation;

use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\OperationCollection;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Transformation;
use Akeneo\AssetManager\Infrastructure\Transformation\Exception\TransformationException;
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

    function it_throws_an_exception_if_no_applier_was_found(
        Transformation $transformation
    ) {
        $transformation->getOperationCollection()->willReturn(
            OperationCollection::create([
                Operation\ColorspaceOperation::create(['colorspace' => 'grey'])
            ])
        );

        $this->shouldThrow(TransformationException::class)->during(
            'transform',
            [
                new File('/my/file/path', false),
                $transformation
            ]
        );
    }

    function it_applies_a_transformation_to_a_file(
        OperationApplier $scaleOperationApplier,
        OperationApplier $thumbnailOperationApplier,
        Transformation $transformation,
        File $file
    ) {
        $scale = Operation\ScaleOperation::create(['ratio' => 50]);
        $thumbnail = Operation\ThumbnailOperation::create(['height' => 100]);
        $transformation->getOperationCollection()->willReturn(OperationCollection::create([$scale, $thumbnail]));
        $transformation->getFilenamePrefix()->willReturn('thumb_');
        $transformation->getFilenameSuffix()->willReturn('-42');

        $file->beConstructedWith(['/my/file/jambon.png', false]);
        $file->getExtension()->willReturn('png');
        $file->getPath()->willReturn('/my/file');
        $file->getBasename('.png')->willReturn('jambon');

        $scaleOperationApplier->apply($file, $scale)->shouldBeCalled()->willReturn($file);
        $thumbnailOperationApplier->apply($file, $thumbnail)->shouldBeCalled()->willReturn($file);

        $transformedFile = new File('/my/file/thumb_jambon-42.png', false);
        $file->move('/my/file', 'thumb_jambon-42.png')->shouldBeCalled()->willReturn($transformedFile);

        $this->transform($file, $transformation)->shouldReturn($transformedFile);
    }
}
