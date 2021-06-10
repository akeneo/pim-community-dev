<?php

namespace spec\Akeneo\AssetManager\Infrastructure\Transformation\Operation;

use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation\ColorspaceOperation;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation\ResizeOperation;
use Akeneo\AssetManager\Infrastructure\Transformation\Operation\OperationApplier;
use Akeneo\AssetManager\Infrastructure\Transformation\Operation\ResizeOperationApplier;
use Liip\ImagineBundle\Binary\BinaryInterface;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use Liip\ImagineBundle\Model\FileBinary;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;

class ResizeOperationApplierSpec extends ObjectBehavior
{
    function let(FilterManager $filterManager, Filesystem $filesystem)
    {
        $this->beConstructedWith($filterManager, $filesystem);
    }

    function it_is_an_operation_applier()
    {
        $this->shouldImplement(OperationApplier::class);
    }

    function it_is_a_resize_operation_applier()
    {
        $this->shouldHaveType(ResizeOperationApplier::class);
    }

    function it_only_supports_resize_operations(
        ResizeOperation $operation,
        ColorspaceOperation $wrongOperation
    ) {
        $this->supports($operation)->shouldReturn(true);
        $this->supports($wrongOperation)->shouldReturn(false);
    }

    function it_applies_a_resize_operation(
        FilterManager $filterManager,
        Filesystem $filesystem,
        ResizeOperation $operation,
        BinaryInterface $computedImage,
        File $file
    ) {
        $file->beConstructedWith(['/path/to/my/file.png', false]);
        $file->getRealPath()->willReturn('/path/to/my/file.png');
        $file->getMimeType()->willReturn('image/png');

        $computedImage->getContent()->willReturn('imageContent');

        $operation->getWidth()->willReturn(800);
        $operation->getHeight()->willReturn(600);

        $filterManager->applyFilters(
            Argument::type(FileBinary::class),
            [
                'filters' => [
                    'resize' => [
                        'size' => [800, 600],
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
