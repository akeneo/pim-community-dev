<?php

declare(strict_types=1);

namespace AkeneoEnterprise\Test\Acceptance\AssetManager\Context;

use Akeneo\AssetManager\Common\Fake\InMemoryAssetRepository;
use Akeneo\AssetManager\Domain\Model\Asset\Asset;
use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ValueCollection;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Behat\Behat\Context\Context;

/**
 * Use this context to create assets, from the Asset Manager.
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2019 Akeneo SAS (https://www.akeneo.com)
 */
class AssetCreation implements Context
{
    /** @var InMemoryAssetRepository */
    private $assetRepository;

    public function __construct(InMemoryAssetRepository $assetRepository)
    {
        $this->assetRepository = $assetRepository;
    }

    /**
     * @Given /^there are more than 50 assets in this asset family$/
     */
    public function thereAreMoreThanAssetsInThisAssetFamily()
    {
        for ($i = 0; $i < 60; $i++) {
            $asset = Asset::create(
                AssetIdentifier::fromString(sprintf('designer_%s', $i)),
                AssetFamilyIdentifier::fromString('designer'),
                AssetCode::fromString(sprintf('designer_%s', $i)),
                ValueCollection::fromValues([])
            );

            $this->assetRepository->create($asset);
        }
    }
}
