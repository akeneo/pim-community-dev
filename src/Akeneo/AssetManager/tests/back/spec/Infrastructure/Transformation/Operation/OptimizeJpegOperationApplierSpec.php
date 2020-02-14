<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spec\Akeneo\AssetManager\Infrastructure\Transformation\Operation;

use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation\OptimizeJpegOperation;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation\ResizeOperation;
use Akeneo\AssetManager\Infrastructure\Transformation\Operation\OperationApplier;
use Akeneo\AssetManager\Infrastructure\Transformation\Operation\OptimizeJpegOperationApplier;
use Liip\ImagineBundle\Binary\BinaryInterface;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use Liip\ImagineBundle\Model\FileBinary;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\File;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
class OptimizeJpegOperationApplierSpec extends ObjectBehavior
{
    function let(FilterManager $filterManager, Filesystem $filesystem)
    {
        $this->beConstructedWith($filterManager, $filesystem, false);
    }

    function it_is_an_operation_applier()
    {
        $this->shouldImplement(OperationApplier::class);
    }

    function it_is_an_optimize_operation_applier()
    {
        $this->shouldHaveType(OptimizeJpegOperationApplier::class);
    }

    function it_only_supports_optimize_operations(OptimizeJpegOperation $operation, ResizeOperation $wrongOperation)
    {
        $this->supports($operation)->shouldReturn(true);
        $this->supports($wrongOperation)->shouldReturn(false);
    }

    function it_applies_an_optimize_operation(
        FilterManager $filterManager,
        Filesystem $filesystem,
        OptimizeJpegOperation $operation,
        BinaryInterface $computedImage,
        File $file
    ) {
        $file->beConstructedWith(['/path/to/my/file.png', false]);
        $file->getRealPath()->willReturn('/path/to/my/file.png');
        $file->getMimeType()->willReturn('image/png');
        $file->getExtension()->willReturn('png');

        $computedImage->getContent()->willReturn('imageContent');
        $computedImage->getMimeType()->willReturn('image/jpeg');

        $operation->getQuality()->willReturn(70);

        $filterManager->applyPostProcessors(
            Argument::type(FileBinary::class),
            [
                'post_processors' => [
                    'convert_to_jpg' => [
                        'quality' => 70,
                    ],
                ],
            ]
        )->shouldBeCalledOnce()->willReturn($computedImage);
        $filesystem->dumpFile('/path/to/my/file.png', 'imageContent')->shouldBeCalled();
        $filesystem->rename('/path/to/my/file.png', '/path/to/my/file.jpg')->shouldBeCalled();

        // The test fails because the file /path/to/my/file.jpg does not exist in local, but the goal is
        // to check that a new JPG file is tried to be returned. The below exception proves that.
        $this->shouldThrow(new FileNotFoundException('/path/to/my/file.jpg'))
            ->during('apply', [$file, $operation]);
    }
}
