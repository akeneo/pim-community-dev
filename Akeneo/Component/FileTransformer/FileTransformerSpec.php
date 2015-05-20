<?php

namespace spec\Akeneo\Component\FileTransformer;

use Akeneo\Component\FileTransformer\Exception\InvalidFileTransformerOptionsException;
use Akeneo\Component\FileTransformer\Transformation\TransformationInterface;
use Akeneo\Component\FileTransformer\Transformation\TransformationRegistry;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

// TODO: Find a way to spec getOutputFile()
class FileTransformerSpec extends ObjectBehavior
{
    function let(TransformationRegistry $registry)
    {
        $this->beConstructedWith($registry);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('\Akeneo\Component\FileTransformer\FileTransformer');
    }

    function it_is_a_file_transformer()
    {
        $this->shouldImplement('\Akeneo\Component\FileTransformer\FileTransformerInterface');
    }

    function it_transforms_a_file_with_the_following_options(
        $registry,
        \SplFileInfo $file,
        TransformationInterface $thumbnailTransformation,
        TransformationInterface $colorSpaceTransformation
    ) {
        $rawTransformations = [
            'thumbnail'  => ['width' => 100, 'height' => 100],
            'colorspace' => ['colorspace' => 'gray']
        ];

        $file->getPathname()->willReturn(__FILE__);

        $registry->get('thumbnail', 'text/x-php')->willReturn($thumbnailTransformation);
        $registry->get('colorspace', 'text/x-php')->willReturn($colorSpaceTransformation);

        $thumbnailTransformation->transform($file, ['width' => 100, 'height' => 100])->shouldBeCalled();
        $colorSpaceTransformation->transform($file, ['colorspace' => 'gray'])->shouldBeCalled();

        $this->transform($file, $rawTransformations)->shouldReturn($file);
    }

    function it_transforms_a_file_with_the_following_options_and_save_it_to_a_different_output(
        $registry,
        \SplFileInfo $file,
        TransformationInterface $thumbnailTransformation,
        TransformationInterface $colorSpaceTransformation
    ) {
        $outputFilename = uniqid();

        $rawTransformations = [
            'thumbnail'  => ['width' => 100, 'height' => 100],
            'colorspace' => ['colorspace' => 'gray']
        ];

        $file->getPathname()->willReturn(__FILE__);
        $file->getPath()->willReturn(__DIR__);

        $registry->get('thumbnail', 'text/x-php')->willReturn($thumbnailTransformation);
        $registry->get('colorspace', 'text/x-php')->willReturn($colorSpaceTransformation);

        $thumbnailTransformation->transform(Argument::any(), ['width' => 100, 'height' => 100])->shouldBeCalled();
        $colorSpaceTransformation->transform(Argument::any(), ['colorspace' => 'gray'])->shouldBeCalled();

        $outputFile = $this->transform($file, $rawTransformations, $outputFilename);
        $outputFile->getFilename()->shouldBe($outputFilename);

        unlink(__DIR__ . '/' . $outputFilename);
    }
}
