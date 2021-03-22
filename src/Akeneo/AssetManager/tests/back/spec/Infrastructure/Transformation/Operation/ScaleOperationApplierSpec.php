<?php

namespace spec\Akeneo\AssetManager\Infrastructure\Transformation\Operation;

use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation\ResizeOperation;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation\ScaleOperation;
use Akeneo\AssetManager\Infrastructure\Transformation\Operation\OperationApplier;
use Akeneo\AssetManager\Infrastructure\Transformation\Operation\ScaleOperationApplier;
use Liip\ImagineBundle\Binary\BinaryInterface;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use Liip\ImagineBundle\Model\FileBinary;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;

class ScaleOperationApplierSpec extends ObjectBehavior
{
    function let(FilterManager $filterManager, Filesystem $filesystem)
    {
        $this->beConstructedWith($filterManager, $filesystem);
    }

    function it_is_an_operation_applier()
    {
        $this->shouldImplement(OperationApplier::class);
    }

    function it_is_a_scale_operation_applier()
    {
        $this->shouldHaveType(ScaleOperationApplier::class);
    }

    function it_only_supports_scale_operations(
        ScaleOperation $operation,
        ResizeOperation $wrongOperation
    ) {
        $this->supports($operation)->shouldReturn(true);
        $this->supports($wrongOperation)->shouldReturn(false);
    }

    function it_applies_a_scale_operation_with_ratio(
        FilterManager $filterManager,
        Filesystem $filesystem,
        ScaleOperation $operation,
        BinaryInterface $computedImage,
        File $file
    ) {
        $file->beConstructedWith(['/path/to/my/file.png', false]);
        $file->getRealPath()->willReturn('/path/to/my/file.png');
        $file->getMimeType()->willReturn('image/png');

        $computedImage->getContent()->willReturn('imageContent');
        $operation->getRatioPercent()->willReturn(75);

        $filterManager->applyFilters(
            Argument::type(FileBinary::class),
            [
                'filters' => [
                    'scale' => [
                        'to' => 0.75,
                    ],
                ],
                'quality' => 100,
                'format' => 'png'
            ]
        )->shouldBeCalledOnce()->willReturn($computedImage);
        $filesystem->dumpFile('/path/to/my/file.png', 'imageContent')->shouldBeCalled();

        $this->apply($file, $operation)->shouldReturn($file);
    }

    function it_applies_a_scale_operation_with_dimensions(
        FilterManager $filterManager,
        Filesystem $filesystem,
        ScaleOperation $operation,
        BinaryInterface $computedImage,
        File $file
    ) {
        $file->beConstructedWith(['/path/to/my/file.png', false]);
        $file->getRealPath()->willReturn('/path/to/my/file.png');
        $file->getMimeType()->willReturn('image/png');

        $computedImage->getContent()->willReturn('imageContent');
        $operation->getRatioPercent()->willReturn(null);
        $operation->getWidth()->willReturn(800);
        $operation->getHeight()->willReturn(600);

        $filterManager->applyFilters(
            Argument::type(FileBinary::class),
            [
                'filters' => [
                    'scale' => [
                        'dim' => [800, 600],
                    ],
                ],
                'quality' => 100,
                'format' => 'png'
            ]
        )->shouldBeCalledOnce()->willReturn($computedImage);
        $filesystem->dumpFile('/path/to/my/file.png', 'imageContent')->shouldBeCalled();

        $this->apply($file, $operation)->shouldReturn($file);
    }
}
