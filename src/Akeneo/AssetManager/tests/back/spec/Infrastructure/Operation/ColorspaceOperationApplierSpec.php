<?php

namespace spec\Akeneo\AssetManager\Infrastructure\Operation;

use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation\ColorspaceOperation;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation\ResizeOperation;
use Akeneo\AssetManager\Infrastructure\Operation\ColorspaceOperationApplier;
use Liip\ImagineBundle\Binary\BinaryInterface;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use Liip\ImagineBundle\Model\FileBinary;
use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpFoundation\File\File;
use Webmozart\Assert\Assert;

class ColorspaceOperationApplierSpec extends ObjectBehavior
{
    function let(FilterManager $filterManager)
    {
        $this->beConstructedWith($filterManager);
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

        $result = $this->apply($file, $operation);
        $result->shouldBeAnInstanceOf(File::class);
        $result->getPath()->shouldEqual(sys_get_temp_dir());
        $result->getFilename()->shouldStartWith('asset_manager_operation');
        Assert::eq(file_get_contents($result->getPathname()->getWrappedObject()), ('imageContent'));
    }
}
