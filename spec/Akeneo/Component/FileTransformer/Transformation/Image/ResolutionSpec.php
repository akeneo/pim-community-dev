<?php
declare(strict_types=1);

namespace spec\Akeneo\Component\FileTransformer\Transformation\Image;

use Akeneo\Component\FileTransformer\Options\TransformationOptionsResolverInterface;
use Akeneo\Component\FileTransformer\Transformation\AbstractTransformation;
use Akeneo\Component\FileTransformer\Transformation\Image\ImageMagickLauncher;
use PhpSpec\ObjectBehavior;

class ResolutionSpec extends ObjectBehavior
{
    function let(
        TransformationOptionsResolverInterface $optionsResolver,
        ImageMagickLauncher $launcher
    )
    {
        $this->beConstructedWith($optionsResolver, $launcher, [
            'image/jpeg',
            'image/tiff',
            'image/png'
        ]);
    }

    function it_is_a_transformation()
    {
        $this->shouldBeAnInstanceOf(AbstractTransformation::class);
    }

    function it_has_a_name()
    {
        $this->getName()->shouldReturn('resolution');
    }

    function it_transforms_a_file_with_options(
        TransformationOptionsResolverInterface $optionsResolver,
        ImageMagickLauncher $launcher,
        \SplFileInfo $fileInfo
    ) {
        $options = [
            'resolution' => 72,
            'resolution-unit' => 'ppc'
        ];

        $optionsResolver->resolve($options)->willReturn($options);
        $fileInfo->getPathname()->willReturn('/tmp/toto.jpg');

        $launcher->convert('', '/tmp/toto.jpg', '-density 72x72 -units PixelsPerCentimeter')
            ->shouldBeCalled();

        $this->transform($fileInfo, $options);


        $options = [
            'resolution' => 120,
            'resolution-unit' => 'ppi'
        ];

        $optionsResolver->resolve($options)->willReturn($options);

        $launcher->convert('', '/tmp/toto.jpg', '-density 120x120 -units PixelsPerInch')
            ->shouldBeCalled();

        $this->transform($fileInfo, $options);
    }
}
