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

namespace Akeneo\AssetManager\Common\Fake;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\Asset\FindAssetIdentifiersByAssetFamilyInterface;

class InMemoryFindAssetIdentifiersByAssetFamily implements FindAssetIdentifiersByAssetFamilyInterface
{
    private InMemoryAssetRepository $assetRepository;

    public function __construct(InMemoryAssetRepository $assetRepository)
    {
        $this->assetRepository = $assetRepository;
    }

    public function find(AssetFamilyIdentifier $assetFamilyIdentifier): \Iterator
    {
        foreach ($this->assetRepository->all() as $asset) {
            if ($asset->getAssetFamilyIdentifier()->equals($assetFamilyIdentifier)) {
                yield $asset->getIdentifier();
            }
        }
    }
}
