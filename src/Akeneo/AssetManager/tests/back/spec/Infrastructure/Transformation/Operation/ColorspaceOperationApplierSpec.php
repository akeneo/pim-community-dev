<?php

namespace spec\Akeneo\AssetManager\Infrastructure\Transformation\Operation;

use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation\ColorspaceOperation;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation\ResizeOperation;
use Akeneo\AssetManager\Infrastructure\Transformation\Operation\ColorspaceOperationApplier;
use Akeneo\AssetManager\Infrastructure\Transformation\Operation\TemporaryFileFactory;
use Liip\ImagineBundle\Binary\BinaryInterface;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use Liip\ImagineBundle\Model\FileBinary;
use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpFoundation\File\File;

class ColorspaceOperationApplierSpec extends ObjectBehavior
{
    function let(FilterManager $filterManager, TemporaryFileFactory $temporaryFileFactory)
    {
        $this->beConstructedWith($filterManager, $temporaryFileFactory);
        $this->shouldHaveType(ColorspaceOperationApplier::class);
    }

    function it_supports_only_colorspace_operation(
        ColorspaceOperation $operation,
        ResizeOperation $wrongOperation
    ) {
        $this->supports($operation)->shouldReturn(true);
        $this->supports($wrongOperation)->shouldReturn(false);
    }

    function it_apply_colorspace(
        FilterManager $filterManager,
        TemporaryFileFactory $temporaryFileFactory,
        ColorspaceOperation $operation,
        BinaryInterface $computedImage
    ) {
        $file = new File(__DIR__ . '/akeneo.png');
        $operation->getColorspace()->willReturn('cmyk');
        $image = new FileBinary($file->getPath(), $file->getMimeType());
        $filterManager->applyFilters($image, [
            'filters' => [
                'colorspace' => [
                    'colorspace' => 'cmyk'
                ]
            ]
        ])->shouldBeCalledOnce()->willReturn($computedImage);
        $computedImage->getContent()->willReturn('imageContent');
        $temporaryFileFactory->createFromContent('imageContent')->shouldBeCalled()->willReturn($file);

        $this->apply($file, $operation);
    }
}
