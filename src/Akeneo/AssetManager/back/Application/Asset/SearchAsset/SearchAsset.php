<?php
declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Application\Asset\SearchAsset;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\Asset\AssetQuery;
use Akeneo\AssetManager\Domain\Query\Asset\CountAssetsInterface;
use Akeneo\AssetManager\Domain\Query\Asset\FindAssetItemsForIdentifiersAndQueryInterface;
use Akeneo\AssetManager\Domain\Query\Asset\FindIdentifiersForQueryInterface;
use Akeneo\AssetManager\Domain\Query\Asset\IdentifiersForQueryResult;
use Akeneo\AssetManager\Domain\Query\Asset\SearchAssetResult;

/**
 * This service takes a asset search query and will return a collection of asset items.
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class SearchAsset
{
    private FindIdentifiersForQueryInterface $findIdentifiersForQuery;

    private FindAssetItemsForIdentifiersAndQueryInterface $findAssetItemsForIdentifiersAndQuery;

    private CountAssetsInterface $countAssets;

    public function __construct(
        FindIdentifiersForQueryInterface $findIdentifiersForQuery,
        FindAssetItemsForIdentifiersAndQueryInterface $findAssetItemsForIdentifiersAndQuery,
        CountAssetsInterface $countAssets
    ) {
        $this->findIdentifiersForQuery = $findIdentifiersForQuery;
        $this->findAssetItemsForIdentifiersAndQuery = $findAssetItemsForIdentifiersAndQuery;
        $this->countAssets = $countAssets;
    }

    public function __invoke(AssetQuery $query): SearchAssetResult
    {
        /** @var IdentifiersForQueryResult $result */
        $result = $this->findIdentifiersForQuery->find($query);
        $assets = $this->findAssetItemsForIdentifiersAndQuery->find($result->identifiers, $query);
        $totalCount = $this->countTotalAssets($query);

        return new SearchAssetResult($assets, $result->matchesCount, $totalCount);
    }

    private function countTotalAssets(AssetQuery $assetQuery): int
    {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString($assetQuery->getFilter('asset_family')['value']);

        return $this->countAssets->forAssetFamily($assetFamilyIdentifier);
    }
}
