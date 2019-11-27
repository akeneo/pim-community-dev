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

use Akeneo\AssetManager\Domain\Model\Asset\Asset;
use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamily;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\AssetFamily\Transformation\GetTransformations;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyNotFoundException;
use Akeneo\AssetManager\Domain\Repository\AssetNotFoundException;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class InMemoryGetTransformations implements GetTransformations
{
    /** @var InMemoryAssetRepository */
    private $assetRepository;

    /** @var InMemoryAssetFamilyRepository */
    private $assetFamilyRepository;

    public function __construct(
        InMemoryAssetRepository $assetRepository,
        InMemoryAssetFamilyRepository $assetFamilyRepository
    ) {
        $this->assetRepository = $assetRepository;
        $this->assetFamilyRepository = $assetFamilyRepository;
    }

    public function fromAssetIdentifiers(array $assetIdentifiers): array
    {
        $transformations = [];

        /** @var AssetIdentifier $assetIdentifier */
        foreach ($assetIdentifiers as $assetIdentifier) {
            $asset = $this->getAssetOrNull($assetIdentifier);
            if ($asset instanceof Asset) {
                $assetFamilyIdentifier = $asset->getAssetFamilyIdentifier();
                $assetFamily = $this->getAssetFamilyOrNull($assetFamilyIdentifier);
                if ($assetFamily instanceof AssetFamily) {
                    $transformations[$assetIdentifier->__toString()] = $assetFamily->getTransformationCollection();
                }
            }
        }

        return $transformations;
    }

    private function getAssetOrNull(AssetIdentifier $assetIdentifier): ?Asset
    {
        try {
            return $this->assetRepository->getByIdentifier($assetIdentifier);
        } catch (AssetNotFoundException $e) {
            return null;
        }
    }

    private function getAssetFamilyOrNull(AssetFamilyIdentifier $assetFamilyIdentifier): ?AssetFamily
    {
        try {
            return $this->assetFamilyRepository->getByIdentifier($assetFamilyIdentifier);
        } catch (AssetFamilyNotFoundException $e) {
            return null;
        }
    }
}
