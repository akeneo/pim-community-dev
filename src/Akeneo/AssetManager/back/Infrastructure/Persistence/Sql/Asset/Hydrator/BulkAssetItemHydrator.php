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

namespace Akeneo\AssetManager\Infrastructure\Persistence\Sql\Asset\Hydrator;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\Asset\AssetQuery;
use Akeneo\AssetManager\Domain\Query\Asset\FindAssetLabelsByIdentifiersInterface;
use Akeneo\AssetManager\Domain\Query\Attribute\FindValueKeysByAttributeTypeInterface;

/**
 * Bulk hydrator of AssetItems.
 * We take the advantage of bulk to unify heavy operations such as retrieving linked asset labels.
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2019 Akeneo SAS (https://www.akeneo.com)
 */
class BulkAssetItemHydrator
{
    private AssetItemHydratorInterface $assetItemHydrator;

    private FindValueKeysByAttributeTypeInterface $findValueKeysByAttributeType;

    private FindAssetLabelsByIdentifiersInterface $findAssetLabelsByIdentifiers;

    public function __construct(
        AssetItemHydratorInterface $assetItemHydrator,
        FindValueKeysByAttributeTypeInterface $findValueKeysByAttributeType,
        FindAssetLabelsByIdentifiersInterface $findAssetLabelsByIdentifiers
    ) {
        $this->assetItemHydrator = $assetItemHydrator;
        $this->findValueKeysByAttributeType = $findValueKeysByAttributeType;
        $this->findAssetLabelsByIdentifiers = $findAssetLabelsByIdentifiers;
    }

    public function hydrateAll(array $rows, AssetQuery $query): array
    {
        $assetItems = [];

        $assetFamilyFilter = $query->getFilter('asset_family');
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString($assetFamilyFilter['value']);

        $labelsIndexedByAssetIdentifier = $this->getLabelsForIdentifier($rows, $assetFamilyIdentifier);

        foreach ($rows as $row) {
            $assetItems[] = $this->assetItemHydrator->hydrate($row, $query, ['labels' => $labelsIndexedByAssetIdentifier]);
        }

        return $assetItems;
    }

    private function getLabelsForIdentifier(array $rows, AssetFamilyIdentifier $assetFamilyIdentifier): array
    {
        $assetIdentifiers = [];
        $assetLinkValueKeys = $this->findValueKeysByAttributeType->find(
            $assetFamilyIdentifier,
            ['asset', 'asset_collection']
        );
        $mask = array_flip($assetLinkValueKeys);

        foreach ($rows as $row) {
            $valueCollection = json_decode($row['value_collection'], true);
            $rawAssetValues = array_intersect_key($valueCollection, $mask);

            foreach ($rawAssetValues as $rawValue) {
                $data = is_array($rawValue['data']) ? $rawValue['data'] : [$rawValue['data']];
                $assetIdentifiers = array_merge($assetIdentifiers, $data);
            }
        }

        return $this->findAssetLabelsByIdentifiers->find($assetIdentifiers);
    }
}
