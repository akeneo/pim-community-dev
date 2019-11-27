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
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation\ColorspaceOperation;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use Liip\ImagineBundle\Model\FileBinary;
use Symfony\Component\HttpFoundation\File\File;
use Webmozart\Assert\Assert;

class ColorspaceOperationApplier implements OperationApplier
{
    /** @var FilterManager */
    private $filterManager;

    public function __construct(FilterManager $filterManager)
    {
        $this->filterManager = $filterManager;
    }

    public function supports(Operation $operation): bool
    {
        return $operation instanceof ColorspaceOperation;
    }

    /**
     * @param File $file
     * @param ColorspaceOperation $colorspaceOperation
     * @return File
     */
    public function apply(File $file, Operation $colorspaceOperation): File
    {
        Assert::isInstanceOf($colorspaceOperation, ColorspaceOperation::class);

        $image = new FileBinary($file->getPath(), $file->getMimeType());
        $computedImage = $this->filterManager->applyFilters($image, [
            'filters' => [
                'colorspace' => [
                    'colorspace' => $colorspaceOperation->getColorspace()
                ]
            ]
        ]);

        $tmpFile = tempnam(sys_get_temp_dir(), 'asset_manager_operation');
        file_put_contents($tmpFile, $computedImage->getContent());

        return new File($tmpFile, false);
    }
}
