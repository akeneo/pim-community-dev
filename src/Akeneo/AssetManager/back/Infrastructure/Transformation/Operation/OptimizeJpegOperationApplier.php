<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Infrastructure\Transformation\Operation;

use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation\OptimizeJpegOperation;
use Akeneo\AssetManager\Infrastructure\Filesystem\PostProcessor\ConvertToJPGPostProcessor;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use Liip\ImagineBundle\Model\FileBinary;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Webmozart\Assert\Assert;

class OptimizeJpegOperationApplier implements OperationApplier
{
    private FilterManager $filterManager;

    private Filesystem $filesystem;

    public function __construct(FilterManager $filterManager, Filesystem $filesystem)
    {
        $this->filterManager = $filterManager;
        $this->filesystem = $filesystem;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Operation $operation): bool
    {
        return $operation instanceof OptimizeJpegOperation;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(File $file, Operation $optimizeOperation): File
    {
        Assert::isInstanceOf($optimizeOperation, OptimizeJpegOperation::class);

        $oldMimeType = $file->getMimeType();
        $image = new FileBinary($file->getRealPath(), $oldMimeType);
        $computedImage = $this->filterManager->applyPostProcessors(
            $image,
            [
                'post_processors' => [
                    'convert_to_jpg' => [
                        'quality' => $optimizeOperation->getQuality(),
                    ],
                ],
            ]
        );
        $this->filesystem->dumpFile($file->getRealPath(), $computedImage->getContent());

        if ($computedImage->getMimeType() === ConvertToJPGPostProcessor::MIME_TYPE && $file->getExtension() !== 'jpg') {
            return $this->computeFileWithNewJPGExtension($file);
        }

        return $file;
    }

    private function computeFileWithNewJPGExtension(File $file): File
    {
        $extensionPosition = strrpos($file->getRealPath(), $file->getExtension());

        if ($extensionPosition !== false) {
            $newPath = substr_replace($file->getRealPath(), 'jpg', $extensionPosition, strlen($file->getExtension()));
            $this->filesystem->rename($file->getRealPath(), $newPath);

            return new File($newPath);
        }

        return $file;
    }
}
