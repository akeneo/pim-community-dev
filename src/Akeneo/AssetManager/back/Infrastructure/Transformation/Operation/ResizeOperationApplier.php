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
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation\ResizeOperation;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use Liip\ImagineBundle\Model\FileBinary;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Webmozart\Assert\Assert;

class ResizeOperationApplier implements OperationApplier
{
    /** @var FilterManager */
    private $filterManager;

    /** @var Filesystem */
    private $filesystem;

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
        return $operation instanceof ResizeOperation;
    }

    /**
     * {@inheritdoc}}
     */
    public function apply(File $file, Operation $resizeOperation): File
    {
        Assert::isInstanceOf($resizeOperation, ResizeOperation::class);

        $image = new FileBinary($file->getRealPath(), $file->getMimeType());
        $computedImage = $this->filterManager->applyFilters(
            $image,
            [
                'filters' => [
                    'resize' => [
                        'size' => [$resizeOperation->getWidth(), $resizeOperation->getHeight()],
                    ],
                ],
                'quality' => 100,
                'format' => 'png'
            ]
        );
        $this->filesystem->dumpFile($file->getRealPath(), $computedImage->getContent());

        return $file;
    }
}
