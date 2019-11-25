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

namespace Akeneo\AssetManager\Application\Asset\ComputeTransformationsAssets;

use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
use Webmozart\Assert\Assert;

class ComputeTransformationsExecutor
{
    /**
     * @param AssetIdentifier[] $assetIdentifiers
     */
    public function execute(array $assetIdentifiers)
    {
        Assert::allIsInstanceOf($assetIdentifiers, AssetIdentifier::class);

        throw new \Exception('TODO; to implement');
    }
}
