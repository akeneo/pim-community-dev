<?php

namespace spec\Akeneo\AssetManager\Infrastructure\Transformation\Operation;

use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation\ColorspaceOperation;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation\ResolutionOperation;
use Akeneo\AssetManager\Infrastructure\Transformation\Operation\ResolutionOperationApplier;
use Akeneo\AssetManager\Infrastructure\Transformation\Operation\TemporaryFileFactory;
use Liip\ImagineBundle\Binary\BinaryInterface;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use Liip\ImagineBundle\Model\FileBinary;
use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpFoundation\File\File;

class ResolutionOperationApplierSpec extends ObjectBehavior
{
    function let(FilterManager $filterManager, TemporaryFileFactory $temporaryFileFactory)
    {
        $this->beConstructedWith($filterManager, $temporaryFileFactory);
        $this->shouldHaveType(ResolutionOperationApplier::class);
    }

    function it_supports_only_resolution_operation(
        ResolutionOperation $operation,
        ColorspaceOperation $wrongOperation
    ) {
        $this->supports($operation)->shouldReturn(true);
        $this->supports($wrongOperation)->shouldReturn(false);
    }

    function it_apply_resolution(
        FilterManager $filterManager,
        TemporaryFileFactory $temporaryFileFactory,
        ResolutionOperation $operation,
        BinaryInterface $computedImage
    ) {
        $file = new File(__DIR__ . '/akeneo.png');
        $operation->getResolutionUnit()->willReturn('ppc');
        $operation->getResolutionX()->willReturn(800);
        $operation->getResolutionY()->willReturn(600);
        $image = new FileBinary($file->getPath(), $file->getMimeType());
        $filterManager->applyFilters($image, [
            'filters' => [
                'resample' => [
                    'unit' => 'ppc',
                    'x' => 800,
                    'y' => 600
                ]
            ]
        ])->shouldBeCalledOnce()->willReturn($computedImage);
        $computedImage->getContent()->willReturn('imageContent');
        $temporaryFileFactory->createFromContent('imageContent')->shouldBeCalled()->willReturn($file);

        $this->apply($file, $operation);
    }
}
