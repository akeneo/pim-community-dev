<?php

declare(strict_types=1);

namespace spec\Akeneo\Tool\Component\FileTransformer;

use Akeneo\Tool\Component\FileTransformer\Transformation\TransformationInterface;
use Akeneo\Tool\Component\FileTransformer\Transformation\TransformationRegistry;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

// TODO: Find a way to spec getOutputFile()
class FileTransformerSpec extends ObjectBehavior
{
    public function let(TransformationRegistry $registry): void
    {
        $this->beConstructedWith($registry);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType('\Akeneo\Tool\Component\FileTransformer\FileTransformer');
    }

    public function it_is_a_file_transformer(): void
    {
        $this->shouldImplement('\Akeneo\Tool\Component\FileTransformer\FileTransformerInterface');
    }

    public function it_transforms_a_file_with_the_following_options(
        $registry,
        \SplFileInfo $inputFile,
        TransformationInterface $thumbnailTransformation,
        TransformationInterface $colorSpaceTransformation
    ): void {
        $rawTransformations = [
            'thumbnail' => ['width' => 100, 'height' => 100],
            'colorspace' => ['colorspace' => 'gray'],
        ];

        $inputFile->getPathname()->willReturn(__FILE__);

        $registry->has('thumbnail', 'text/x-php')->willReturn(true);
        $registry->has('colorspace', 'text/x-php')->willReturn(true);
        $registry->get('thumbnail', 'text/x-php')->willReturn($thumbnailTransformation);
        $registry->get('colorspace', 'text/x-php')->willReturn($colorSpaceTransformation);

        $thumbnailTransformation->transform($inputFile, ['width' => 100, 'height' => 100])->shouldBeCalled();
        $colorSpaceTransformation->transform($inputFile, ['colorspace' => 'gray'])->shouldBeCalled();

        $this->transform($inputFile, $rawTransformations)->shouldReturn($inputFile);
    }

    public function it_transforms_a_file_with_the_following_options_and_save_it_to_a_different_output(
        $registry,
        \SplFileInfo $inputFile,
        TransformationInterface $thumbnailTransformation,
        TransformationInterface $colorSpaceTransformation
    ): void {
        $outputFilename = uniqid();

        $rawTransformations = [
            'thumbnail' => ['width' => 100, 'height' => 100],
            'colorspace' => ['colorspace' => 'gray'],
        ];

        $inputFile->getPathname()->willReturn(__FILE__);
        $inputFile->getPath()->willReturn(sys_get_temp_dir());

        $registry->has('thumbnail', 'text/x-php')->willReturn(true);
        $registry->has('colorspace', 'text/x-php')->willReturn(true);
        $registry->get('thumbnail', 'text/x-php')->willReturn($thumbnailTransformation);
        $registry->get('colorspace', 'text/x-php')->willReturn($colorSpaceTransformation);

        $thumbnailTransformation->transform(Argument::any(), ['width' => 100, 'height' => 100])->shouldBeCalled();
        $colorSpaceTransformation->transform(Argument::any(), ['colorspace' => 'gray'])->shouldBeCalled();

        $outputFile = $this->transform($inputFile, $rawTransformations, $outputFilename);
        $outputFile->getFilename()->shouldBe($outputFilename);

        unlink(sys_get_temp_dir() . DIRECTORY_SEPARATOR . $outputFilename);
    }

    public function it_skips_a_transformation_if_the_mime_type_is_not_supported(
        $registry,
        \SplFileInfo $inputFile,
        TransformationInterface $colorSpaceTransformation
    ): void {
        $outputFilename = uniqid();

        $rawTransformations = [
            'thumbnail' => ['width' => 100, 'height' => 100],
            'colorspace' => ['colorspace' => 'gray'],
        ];

        $inputFile->getPathname()->willReturn(__FILE__);
        $inputFile->getPath()->willReturn(sys_get_temp_dir());

        $registry->has('thumbnail', 'text/x-php')->willReturn(false);
        $registry->has('colorspace', 'text/x-php')->willReturn(true);
        $registry->get('thumbnail', 'text/x-php')->shouldNotBeCalled();
        $registry->get('colorspace', 'text/x-php')->willReturn($colorSpaceTransformation);

        $colorSpaceTransformation->transform(Argument::any(), ['colorspace' => 'gray'])->shouldBeCalled();

        $outputFile = $this->transform($inputFile, $rawTransformations, $outputFilename);
        $outputFile->getFilename()->shouldBe($outputFilename);

        unlink(sys_get_temp_dir() . DIRECTORY_SEPARATOR . $outputFilename);
    }

    public function it_returns_nothing_if_no_transformation_has_been_applied($registry): void
    {
        $inputFile = new \SplFileInfo(__FILE__);

        $rawTransformations = [
            'thumbnail' => ['width' => 100, 'height' => 100],
            'colorspace' => ['colorspace' => 'gray'],
        ];

        $registry->has('thumbnail', 'text/x-php')->willReturn(false);
        $registry->has('colorspace', 'text/x-php')->willReturn(false);

        $this->transform($inputFile, $rawTransformations)->shouldReturn(null);
    }
}
