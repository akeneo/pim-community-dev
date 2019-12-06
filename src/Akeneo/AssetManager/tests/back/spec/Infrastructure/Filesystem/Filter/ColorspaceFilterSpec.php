<?php

namespace spec\Akeneo\AssetManager\Infrastructure\Filesystem\Filter;

use Akeneo\AssetManager\Infrastructure\Filesystem\Filter\ColorspaceFilter;
use Imagine\Image\ImageInterface;
use Imagine\Image\Palette\CMYK;
use Imagine\Image\Palette\RGB;
use PhpSpec\ObjectBehavior;

class ColorspaceFilterSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ColorspaceFilter::class);
    }

    function it_throws_an_exception_if_there_is_no_colorspace(ImageInterface $image)
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('load', [$image, ['foo' => 'bar']]);
    }

    function it_updates_to_cmyk(ImageInterface $image)
    {
        $image->usePalette(new CMYK())->shouldBeCalledOnce();

        $this->load($image, ['colorspace' => 'cmyk'])->shouldReturn($image);
    }

    function it_updates_to_default(ImageInterface $image)
    {
        $image->usePalette(new RGB())->shouldBeCalledOnce();

        $this->load($image, ['colorspace' => 'foo'])->shouldReturn($image);
    }
}
