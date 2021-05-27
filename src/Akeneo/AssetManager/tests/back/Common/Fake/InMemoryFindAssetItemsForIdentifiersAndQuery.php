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

namespace Akeneo\AssetManager\Common\Fake;

use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\Asset\AssetQuery;
use Akeneo\AssetManager\Domain\Query\Asset\FindAssetItemsForIdentifiersAndQueryInterface;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyRepositoryInterface;
use Akeneo\AssetManager\Domain\Repository\AssetNotFoundException;
use Akeneo\AssetManager\Domain\Repository\AssetRepositoryInterface;
use Akeneo\AssetManager\Infrastructure\Persistence\Sql\Asset\Hydrator\BulkAssetItemHydrator;

/**
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class InMemoryFindAssetItemsForIdentifiersAndQuery implements FindAssetItemsForIdentifiersAndQueryInterface
{
    private AssetRepositoryInterface $assetRepository;

    private AssetFamilyRepositoryInterface $assetFamilyRepository;

    private InMemoryFindRequiredValueKeyCollectionForChannelAndLocales $findRequiredValueKeyCollectionForChannelAndLocales;

    private BulkAssetItemHydrator $bulkAssetItemHydrator;

    public function __construct(
        AssetRepositoryInterface $assetRepository,
        AssetFamilyRepositoryInterface $assetFamilyRepository,
        InMemoryFindRequiredValueKeyCollectionForChannelAndLocales $findRequiredValueKeyCollectionForChannelAndLocales,
        BulkAssetItemHydrator $bulkAssetItemHydrator
    ) {
        $this->assetRepository = $assetRepository;
        $this->assetFamilyRepository = $assetFamilyRepository;
        $this->findRequiredValueKeyCollectionForChannelAndLocales = $findRequiredValueKeyCollectionForChannelAndLocales;
        $this->bulkAssetItemHydrator = $bulkAssetItemHydrator;

        $this->findRequiredValueKeyCollectionForChannelAndLocales->setActivatedLocales(['en_US']);
        $this->findRequiredValueKeyCollectionForChannelAndLocales->setActivatedChannels(['ecommerce']);
    }

    /**
     * {@inheritdoc}
     */
    public function find(array $identifiers, AssetQuery $query): array
    {
        $assetFamilyFilter = $query->getFilter('asset_family');
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString($assetFamilyFilter['value']);
        $assetFamily = $this->assetFamilyRepository->getByIdentifier($assetFamilyIdentifier);
        $attributeAsLabel = $assetFamily->getAttributeAsLabelReference();
        $attributeAsMainMedia = $assetFamily->getAttributeAsMainMediaReference();

        $query = AssetQuery::createFromNormalized([
           'locale' => $query->getChannel(),
           'channel' => $query->getLocale(),
           'size' => 20,
           'page' => 0,
           'filters' => [
               [
                   'field' => 'asset_family',
                   'operator' => '=',
                   'value' => $assetFamilyIdentifier->normalize(),
                   'context' => []
               ]
           ]
        ]);

        $normalizedAssetItems = array_values(array_filter(array_map(function (string $identifier) use (
            $attributeAsLabel,
            $attributeAsMainMedia
        ) {
            try {
                $asset = $this->assetRepository->getByIdentifier(AssetIdentifier::fromString($identifier));
            } catch (AssetNotFoundException $exception) {
                return false;
            }

            return [
                'identifier' => (string) $asset->getIdentifier(),
                'asset_family_identifier' => (string) $asset->getAssetFamilyIdentifier(),
                'code' => (string) $asset->getCode(),
                'value_collection' => json_encode($asset->getValues()->normalize()),
                'attribute_as_main_media' => $attributeAsMainMedia->normalize(),
                'attribute_as_label' => $attributeAsLabel->normalize()
            ];
        }, $identifiers)));

        return $this->bulkAssetItemHydrator->hydrateAll($normalizedAssetItems, $query);
    }
}
