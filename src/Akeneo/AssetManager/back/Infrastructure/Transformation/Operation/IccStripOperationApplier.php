<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Infrastructure\Transformation\Operation;

use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation\IccStripOperation;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation\ThumbnailOperation;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use Liip\ImagineBundle\Model\FileBinary;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Webmozart\Assert\Assert;

final class IccStripOperationApplier implements OperationApplier
{
    public function __construct(
        private FilterManager $filterManager,
        private Filesystem $filesystem
    ){
    }

    public function supports(Operation $operation): bool
    {
        return $operation instanceof IccStripOperation;
    }

    public function apply(File $file, Operation $iccStripOperation): File
    {
        Assert::isInstanceOf($iccStripOperation, IccStripOperation::class);

        $image = new FileBinary($file->getRealPath(), $file->getMimeType());
        $computedImage = $this->filterManager->applyFilters($image, [
            'filters' => [
                'icc_strip' => null,
            ],
            'quality' => 100,
            'format' => 'png'
        ]);
        $this->filesystem->dumpFile($file->getRealPath(), $computedImage->getContent());

        return $file;
    }
}