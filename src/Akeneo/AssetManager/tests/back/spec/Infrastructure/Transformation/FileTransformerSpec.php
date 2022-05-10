<?php

declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Infrastructure\Transformation;

use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation\ScaleOperation;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation\IccStripOperation;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation\ThumbnailOperation;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation\ColorspaceOperation;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\OperationCollection;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Transformation;
use Akeneo\AssetManager\Infrastructure\Transformation\Exception\TransformationException;
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
        OperationApplier $thumbnailOperationApplier,
        OperationApplier $iccStripOperationApplier
    ) {
        // registry with 'scale' and 'thumbnail' operation appliers
        $scaleOperationApplier->supports(Argument::type(ScaleOperation::class))->willReturn(true);
        $scaleOperationApplier->supports(Argument::type(Operation::class))->willReturn(false);
        $thumbnailOperationApplier->supports(Argument::type(ThumbnailOperation::class))->willReturn(true);
        $thumbnailOperationApplier->supports(Argument::type(Operation::class))->willReturn(false);
        $iccStripOperationApplier->supports(Argument::type(IccStripOperation::class))->willReturn(true);
        $iccStripOperationApplier->supports(Argument::type(Operation::class))->willReturn(false);
        $applierRegistry = new OperationApplierRegistry(
            [$iccStripOperationApplier->getWrappedObject(), $scaleOperationApplier->getWrappedObject(), $thumbnailOperationApplier->getWrappedObject()]
        );

        $this->beConstructedWith($applierRegistry);
    }

    function it_is_a_file_transformer()
    {
        $this->shouldHaveType(FileTransformer::class);
    }

    function it_throws_an_exception_if_no_applier_was_found(
        Transformation $transformation,
        OperationApplier $iccStripOperationApplier,
        File $file
    ) {
        $file->beConstructedWith(['/my/file/jambon.jpg', false]);
        $file->getExtension()->willReturn('jpg');
        $file->getPath()->willReturn('/my/file');
        $file->getBasename('.jpg')->willReturn('jambon');

        $iccStrip = IccStripOperation::create([]);
        $iccStripOperationApplier->apply($file, $iccStrip)->willReturn($file);

        $transformation->getOperationCollection()->willReturn(
            OperationCollection::create([
                $iccStrip,
                ColorspaceOperation::create(['colorspace' => 'grey'])
            ])
        );

        $this->shouldThrow(TransformationException::class)->during(
            'transform',
            [
                $file,
                $transformation
            ]
        );
    }

    function it_applies_a_transformation_to_a_file_and_uses_png_as_extension(
        OperationApplier $scaleOperationApplier,
        OperationApplier $thumbnailOperationApplier,
        OperationApplier $iccStripOperationApplier,
        Transformation $transformation,
        File $file
    ) {
        $iccStrip = IccStripOperation::create([]);
        $scale = ScaleOperation::create(['ratio' => 50]);
        $thumbnail = ThumbnailOperation::create(['height' => 100]);
        $transformation->getOperationCollection()->willReturn(OperationCollection::create([$iccStrip, $scale, $thumbnail]));
        $transformation->getFilenamePrefix()->willReturn('thumb_');
        $transformation->getFilenameSuffix()->willReturn('-42');

        $file->beConstructedWith(['/my/file/jambon.jpg', false]);
        $file->getExtension()->willReturn('jpg');
        $file->getPath()->willReturn('/my/file');
        $file->getBasename('.jpg')->willReturn('jambon');

        $iccStripOperationApplier->apply($file, $iccStrip)->shouldBeCalled()->willReturn($file);
        $scaleOperationApplier->apply($file, $scale)->shouldBeCalled()->willReturn($file);
        $thumbnailOperationApplier->apply($file, $thumbnail)->shouldBeCalled()->willReturn($file);

        $transformedFile = new File('/my/file/thumb_jambon-42.png', false);
        $file->move('/my/file', 'thumb_jambon-42.png')->shouldBeCalled()->willReturn($transformedFile);

        $this->transform($file, $transformation)->shouldReturn($transformedFile);
    }
}
