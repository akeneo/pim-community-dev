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

    function it_transforms_a_file_following_options(
        $registry,
        \SplFileInfo $file,
        TransformationInterface $thumbnailTransformation,
        TransformationInterface $colorSpaceTransformation,
        TransformationInterface $resizeTransformation
    ) {
        $transformationsPipeline = [
            [
                'outputFile' => null,
                'pipeline'   => [
                    'thumbnail'  => ['width' => 100, 'height' => 100],
                    'colorspace' => ['colorspace' => 'gray']
                ]
            ],
            [
                'outputFile' => null,
                'pipeline'   => [
                    'resize' => ['width' => 400, 'height' => 50]
                ]
            ]
        ];

        $file->getPathname()->willReturn(__FILE__);

        $registry->get('thumbnail', 'text/x-php')->willReturn($thumbnailTransformation);
        $registry->get('colorspace', 'text/x-php')->willReturn($colorSpaceTransformation);
        $registry->get('resize', 'text/x-php')->willReturn($resizeTransformation);

        $thumbnailTransformation->transform($file, ['width' => 100, 'height' => 100])->shouldBeCalled();
        $colorSpaceTransformation->transform($file, ['colorspace' => 'gray'])->shouldBeCalled();
        $resizeTransformation->transform($file, ['width' => 400, 'height' => 50])->shouldBeCalled();

        $this->transform($file, $transformationsPipeline);
    }

    function it_throws_an_exception_if_given_options_are_wrong(\SplFileInfo $file)
    {
        $file->getPathname()->willReturn(__FILE__);

        $wrongPipeline1 = [
            [
                'outputFile' => 123,
                'pipeline'   => [
                    'thumbnail'  => ['width' => 100, 'height' => 100],
                    'colorspace' => ['colorspace' => 'gray']
                ]
            ]
        ];
        $this->shouldThrow(
            new InvalidFileTransformerOptionsException(
                'Your options does not fulfil the requirements of the transformation.'
            )
        )->duringTransform($file, $wrongPipeline1);

        $wrongPipeline2 = [
            [
                'outputFile'     => null,
                'somethingWrong' => [
                    'thumbnail'  => ['width' => 100, 'height' => 100],
                    'colorspace' => ['colorspace' => 'gray']
                ]
            ]
        ];
        $this->shouldThrow(
            new InvalidFileTransformerOptionsException(
                'Your options does not fulfil the requirements of the transformation.'
            )
        )->duringTransform($file, $wrongPipeline2);

        $wrongPipeline3 = [
            [
                'outputFile' => null,
                'pipeline'   => 'somethingWrong'
            ]
        ];
        $this->shouldThrow(
            new InvalidFileTransformerOptionsException(
                'Your options does not fulfil the requirements of the transformation.'
            )
        )->duringTransform($file, $wrongPipeline3);
    }
}
