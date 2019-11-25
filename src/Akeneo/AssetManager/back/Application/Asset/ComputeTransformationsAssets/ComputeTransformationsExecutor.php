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
use Akeneo\AssetManager\Domain\Query\AssetFamily\Transformation\FindTransformationsForAsset;
use Webmozart\Assert\Assert;

class ComputeTransformationsExecutor
{
    /** @var FindTransformationsForAsset */
    private $findTransformationsForAsset;

    public function __construct(FindTransformationsForAsset $findTransformationsForAsset)
    {
        $this->findTransformationsForAsset = $findTransformationsForAsset;
    }

    /**
     * @param AssetIdentifier[] $assetIdentifiers
     */
    public function execute(array $assetIdentifiers): void
    {
        Assert::allIsInstanceOf($assetIdentifiers, AssetIdentifier::class);

        $transformationsPerAssetIdentifier = $this->findTransformationsForAsset->fromAssetIdentifiers(
            $assetIdentifiers
        );

        throw new \Exception('TODO; to implement');
    }
}
