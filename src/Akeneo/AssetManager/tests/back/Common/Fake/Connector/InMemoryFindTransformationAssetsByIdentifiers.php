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

namespace Akeneo\AssetManager\Common\Fake\Connector;

use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\TransformationAsset;
use Akeneo\AssetManager\Domain\Query\Asset\FindTransformationAssetsByIdentifiersInterface;

class InMemoryFindTransformationAssetsByIdentifiers implements FindTransformationAssetsByIdentifiersInterface
{
    /** @var TransformationAsset[] */
    private array $transformationAssets;

    public function __construct()
    {
        $this->transformationAssets = [];
    }

    public function find(array $assetIdentifiers): array
    {
        $results = [];
        foreach ($assetIdentifiers as $assetIdentifier) {
            if (isset($this->transformationAssets[$assetIdentifier])) {
                $result = $this->transformationAssets[$assetIdentifier];
                $results[$assetIdentifier] = $result;
            }
        }

        return $results;
    }

    public function save(TransformationAsset $transformationAsset)
    {
        $this->transformationAssets[(string) $transformationAsset->getIdentifier()] = $transformationAsset;
    }
}
