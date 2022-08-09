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

namespace Akeneo\AssetManager\Application\AssetFamily\Transformation;

use Akeneo\AssetManager\Application\AssetFamily\Transformation\Exception\NonApplicableTransformationException;
use Akeneo\AssetManager\Domain\Model\Asset\Asset;
use Akeneo\AssetManager\Domain\Model\Asset\Value\FileData;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Transformation;

interface GetOutdatedVariationSourceInterface
{
    /**
     * @param Asset $asset
     * @param Transformation $transformation
     *
     * @return FileData|null
     *
     * @throws NonApplicableTransformationException
     */
    public function forAssetAndTransformation(Asset $asset, Transformation $transformation): ?FileData;
}
