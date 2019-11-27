<?php

namespace spec\Akeneo\AssetManager\Infrastructure\Transformation\Operation;

use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation\ColorspaceOperation;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation\ResolutionOperation;
use Akeneo\AssetManager\Infrastructure\Transformation\Operation\ResolutionOperationApplier;
use Liip\ImagineBundle\Binary\BinaryInterface;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use Liip\ImagineBundle\Model\FileBinary;
use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpFoundation\File\File;
use Webmozart\Assert\Assert;

class ResolutionOperationApplierSpec extends ObjectBehavior
{
    function let(FilterManager $filterManager)
    {
        $this->beConstructedWith($filterManager);
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

        $result = $this->apply($file, $operation);
        $result->shouldBeAnInstanceOf(File::class);
        $result->getPath()->shouldEqual(sys_get_temp_dir());
        $result->getFilename()->shouldStartWith('asset_manager_operation');
        Assert::eq(file_get_contents($result->getPathname()->getWrappedObject()), ('imageContent'));
    }
}
