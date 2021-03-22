<?php

namespace spec\Akeneo\AssetManager\Infrastructure\Transformation\Operation;

use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation\ResizeOperation;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation\ThumbnailOperation;
use Akeneo\AssetManager\Infrastructure\Transformation\Operation\OperationApplier;
use Akeneo\AssetManager\Infrastructure\Transformation\Operation\ThumbnailOperationApplier;
use Liip\ImagineBundle\Binary\BinaryInterface;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use Liip\ImagineBundle\Model\FileBinary;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;

class ThumbnailOperationApplierSpec extends ObjectBehavior
{
    function let(FilterManager $filterManager, Filesystem $filesystem)
    {
        $this->beConstructedWith($filterManager, $filesystem);
    }

    function it_is_an_operation_applier()
    {
        $this->shouldImplement(OperationApplier::class);
    }

    function it_is_a_thumbnail_operation_applier()
    {
        $this->shouldHaveType(ThumbnailOperationApplier::class);
    }

    function it_only_supports_thumbnail_operations(
        ThumbnailOperation $operation,
        ResizeOperation $wrongOperation
    ) {
        $this->supports($operation)->shouldReturn(true);
        $this->supports($wrongOperation)->shouldReturn(false);
    }

    function it_applies_a_colorspace_operation(
        FilterManager $filterManager,
        Filesystem $filesystem,
        ThumbnailOperation $operation,
        BinaryInterface $computedImage,
        File $file
    ) {
        $file->beConstructedWith(['/path/to/my/file.png', false]);
        $file->getRealPath()->willReturn('/path/to/my/file.png');
        $file->getMimeType()->willReturn('image/png');


        $computedImage->getContent()->willReturn('imageContent');
        $operation->getWidth()->willReturn(100);
        $operation->getHeight()->willReturn(100);


        $filterManager->applyFilters(
            Argument::type(FileBinary::class),
            [
                'filters' => [
                    'thumbnail' => [
                        'size' => [100, 100],
                    ]
                ],
                'quality' => 100,
                'format' => 'png'
            ]
        )->shouldBeCalledOnce()->willReturn($computedImage);
        $filesystem->dumpFile('/path/to/my/file.png', 'imageContent')->shouldBeCalled();

        $this->apply($file, $operation)->shouldReturn($file);
    }
}
