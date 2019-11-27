<?php

namespace spec\Akeneo\AssetManager\Infrastructure\Transformation\Operation;

use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation\ColorspaceOperation;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation\ResizeOperation;
use Akeneo\AssetManager\Infrastructure\Transformation\Operation\ResizeOperationApplier;
use Akeneo\AssetManager\Infrastructure\Transformation\Operation\TemporaryFileFactory;
use Liip\ImagineBundle\Binary\BinaryInterface;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use Liip\ImagineBundle\Model\FileBinary;
use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpFoundation\File\File;

class ResizeOperationApplierSpec extends ObjectBehavior
{
    function let(FilterManager $filterManager, TemporaryFileFactory $temporaryFileFactory)
    {
        $this->beConstructedWith($filterManager, $temporaryFileFactory);
        $this->shouldHaveType(ResizeOperationApplier::class);
    }

    function it_supports_only_resize_operation(
        ResizeOperation $operation,
        ColorspaceOperation $wrongOperation
    ) {
        $this->supports($operation)->shouldReturn(true);
        $this->supports($wrongOperation)->shouldReturn(false);
    }

    function it_apply_resize(
        FilterManager $filterManager,
        TemporaryFileFactory $temporaryFileFactory,
        ResizeOperation $operation,
        BinaryInterface $computedImage
    ) {
        $file = new File(__DIR__ . '/akeneo.png');
        $operation->getWidth()->willReturn(800);
        $operation->getHeight()->willReturn(600);
        $image = new FileBinary($file->getPath(), $file->getMimeType());
        $filterManager->applyFilters($image, [
            'filters' => [
                'resize' => [
                    'size' => [800, 600]
                ]
            ]
        ])->shouldBeCalledOnce()->willReturn($computedImage);
        $computedImage->getContent()->willReturn('imageContent');
        $temporaryFileFactory->createFromContent('imageContent')->shouldBeCalled()->willReturn($file);

        $this->apply($file, $operation);
    }
}
