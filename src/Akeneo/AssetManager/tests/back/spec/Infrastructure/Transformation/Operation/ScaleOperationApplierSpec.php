<?php

namespace spec\Akeneo\AssetManager\Infrastructure\Transformation\Operation;

use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation\ColorspaceOperation;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation\ScaleOperation;
use Akeneo\AssetManager\Infrastructure\Transformation\Operation\ScaleOperationApplier;
use Liip\ImagineBundle\Binary\BinaryInterface;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use Liip\ImagineBundle\Model\FileBinary;
use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpFoundation\File\File;
use Webmozart\Assert\Assert;

class ScaleOperationApplierSpec extends ObjectBehavior
{
    function let(FilterManager $filterManager)
    {
        $this->beConstructedWith($filterManager);
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

        $result = $this->apply($file, $operation);
        $result->shouldBeAnInstanceOf(File::class);
        $result->getPath()->shouldEqual(sys_get_temp_dir());
        $result->getFilename()->shouldStartWith('asset_manager_operation');
        Assert::eq(file_get_contents($result->getPathname()->getWrappedObject()), ('imageContent'));
    }

    function it_apply_scale_by_size(
        FilterManager $filterManager,
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

        $result = $this->apply($file, $operation);
        $result->shouldBeAnInstanceOf(File::class);
        $result->getPath()->shouldEqual(sys_get_temp_dir());
        $result->getFilename()->shouldStartWith('asset_manager_operation');
        Assert::eq(file_get_contents($result->getPathname()->getWrappedObject()), ('imageContent'));
    }
}
