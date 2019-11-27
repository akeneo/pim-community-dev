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
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation\ResolutionOperation;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use Liip\ImagineBundle\Model\FileBinary;
use Symfony\Component\HttpFoundation\File\File;
use Webmozart\Assert\Assert;

class ResolutionOperationApplier implements OperationApplier
{
    /** @var FilterManager */
    private $filterManager;

    /** @var TemporaryFileFactory */
    private $temporaryFileFactory;

    public function __construct(
        FilterManager $filterManager,
        TemporaryFileFactory $temporaryFileFactory
    ) {
        $this->filterManager = $filterManager;
        $this->temporaryFileFactory = $temporaryFileFactory;
    }

    public function supports(Operation $operation): bool
    {
        return $operation instanceof ResolutionOperation;
    }

    /**
     * @param File $file
     * @param ResolutionOperation $resolutionOperation
     * @return File
     */
    public function apply(File $file, Operation $resolutionOperation): File
    {
        Assert::isInstanceOf($resolutionOperation, ResolutionOperation::class);

        $image = new FileBinary($file->getPath(), $file->getMimeType());
        $computedImage = $this->filterManager->applyFilters($image, [
            'filters' => [
                'resample' => [
                    'unit' => $resolutionOperation->getResolutionUnit(),
                    'x' => $resolutionOperation->getResolutionX(),
                    'y' => $resolutionOperation->getResolutionY(),
                ]
            ]
        ]);

        return $this->temporaryFileFactory->createFromContent($computedImage->getContent());
    }
}
