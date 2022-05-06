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

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\TransformationCollection;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyNotFoundException;

interface GetTransformations
{
    /**
     *
     *
     * @throws AssetFamilyNotFoundException
     */
    public function fromAssetFamilyIdentifier(AssetFamilyIdentifier$assetFamilyIdentifier): TransformationCollection;
}
