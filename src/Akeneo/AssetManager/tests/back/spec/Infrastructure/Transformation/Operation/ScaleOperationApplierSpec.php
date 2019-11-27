<?php

namespace spec\Akeneo\AssetManager\Infrastructure\Transformation\Operation;

use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation\ColorspaceOperation;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation\ScaleOperation;
use Akeneo\AssetManager\Infrastructure\Transformation\Operation\ScaleOperationApplier;
use Akeneo\AssetManager\Infrastructure\Transformation\Operation\TemporaryFileFactory;
use Liip\ImagineBundle\Binary\BinaryInterface;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use Liip\ImagineBundle\Model\FileBinary;
use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpFoundation\File\File;

class ScaleOperationApplierSpec extends ObjectBehavior
{
    function let(FilterManager $filterManager, TemporaryFileFactory $temporaryFileFactory)
    {
        $this->beConstructedWith($filterManager, $temporaryFileFactory);
        $this->shouldHaveType(ScaleOperationApplier::class);
    }

    function it_supports_only_scale_operation(
        ScaleOperation $operation,
        ColorspaceOperation $wrongOperation
    ) {
        $this->supports($operation)->shouldReturn(true);
        $this->supports($wrongOperation)->shouldReturn(false);
    }

    function it_apply_scale_by_ratio(
        FilterManager $filterManager,
        TemporaryFileFactory $temporaryFileFactory,
        ScaleOperation $operation,
        BinaryInterface $computedImage
    ) {
        $file = new File(__DIR__ . '/akeneo.png');
        $operation->getRatioPercent()->willReturn(50);
        $operation->getWidth()->willReturn(800);
        $operation->getHeight()->willReturn(600);
        $image = new FileBinary($file->getPath(), $file->getMimeType());
        $filterManager->applyFilters($image, [
            'filters' => [
                'scale' => [
                    'to' => 0.5
                ]
            ]
        ])->shouldBeCalledOnce()->willReturn($computedImage);
        $computedImage->getContent()->willReturn('imageContent');
        $temporaryFileFactory->createFromContent('imageContent')->shouldBeCalled()->willReturn($file);

        $this->apply($file, $operation);
    }

    function it_apply_scale_by_size(
        FilterManager $filterManager,
        TemporaryFileFactory $temporaryFileFactory,
        ScaleOperation $operation,
        BinaryInterface $computedImage
    ) {
        $file = new File(__DIR__ . '/akeneo.png');
        $operation->getRatioPercent()->willReturn(null);
        $operation->getWidth()->willReturn(800);
        $operation->getHeight()->willReturn(600);
        $image = new FileBinary($file->getPath(), $file->getMimeType());
        $filterManager->applyFilters($image, [
            'filters' => [
                'scale' => [
                    'dim' => [800, 600]
                ]
            ]
        ])->shouldBeCalledOnce()->willReturn($computedImage);
        $computedImage->getContent()->willReturn('imageContent');
        $temporaryFileFactory->createFromContent('imageContent')->shouldBeCalled()->willReturn($file);

        $this->apply($file, $operation);
    }
}
