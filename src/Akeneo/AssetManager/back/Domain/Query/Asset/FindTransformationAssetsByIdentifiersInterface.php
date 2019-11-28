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

namespace Akeneo\AssetManager\Domain\Query\Asset;

use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Asset;

interface FindTransformationAssetsByIdentifiersInterface
{
    /**
     * @param string[] $assetIdentifiers
     *
     * @return Asset
     */
    public function find(array $assetIdentifiers): array;
}
