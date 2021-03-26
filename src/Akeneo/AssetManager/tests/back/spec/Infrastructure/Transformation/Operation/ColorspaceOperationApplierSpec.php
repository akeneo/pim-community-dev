<?php

namespace spec\Akeneo\AssetManager\Infrastructure\Transformation\Operation;

use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation\ColorspaceOperation;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation\ResizeOperation;
use Akeneo\AssetManager\Infrastructure\Transformation\Operation\ColorspaceOperationApplier;
use Akeneo\AssetManager\Infrastructure\Transformation\Operation\OperationApplier;
use Liip\ImagineBundle\Binary\BinaryInterface;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use Liip\ImagineBundle\Model\FileBinary;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;

class ColorspaceOperationApplierSpec extends ObjectBehavior
{
    function let(FilterManager $filterManager, Filesystem $filesystem)
    {
        $this->beConstructedWith($filterManager, $filesystem);
    }

    function it_is_an_operation_applier()
    {
        $this->shouldImplement(OperationApplier::class);
    }

    function it_is_a_colorspace_operation_applier()
    {
        $this->shouldHaveType(ColorspaceOperationApplier::class);
    }

    function it_only_supports_colorspace_operations(
        ColorspaceOperation $operation,
        ResizeOperation $wrongOperation
    ) {
        $this->supports($operation)->shouldReturn(true);
        $this->supports($wrongOperation)->shouldReturn(false);
    }

    function it_applies_a_colorspace_operation(
        FilterManager $filterManager,
        Filesystem $filesystem,
        ColorspaceOperation $operation,
        BinaryInterface $computedImage,
        File $file
    ) {
        $file->beConstructedWith(['/path/to/my/file.png', false]);
        $file->getRealPath()->willReturn('/path/to/my/file.png');
        $file->getMimeType()->willReturn('image/png');

        $computedImage->getContent()->willReturn('imageContent');
        $operation->getColorspace()->willReturn('cmyk');

        $filterManager->applyFilters(
            Argument::type(FileBinary::class),
            [
                'filters' => [
                    'colorspace' => [
                        'colorspace' => 'cmyk',
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
