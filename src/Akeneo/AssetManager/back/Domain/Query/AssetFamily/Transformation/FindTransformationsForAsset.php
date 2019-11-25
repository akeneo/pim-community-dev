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

namespace Akeneo\AssetManager\Domain\Query\AssetFamily\Transformation;

use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;

interface FindTransformationsForAsset
{
    /**
     * Returns an indexed array with asset string identifier as key
     * and an instance of TransformationCollection in value.
     *
     * @param AssetIdentifier[] $assetIdentifiers
     * @return array
     */
    public function fromAssetIdentifiers(array $assetIdentifiers): array;
}
