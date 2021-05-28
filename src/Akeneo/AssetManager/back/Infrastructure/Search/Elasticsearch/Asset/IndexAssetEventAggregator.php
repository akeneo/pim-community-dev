<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Infrastructure\Search\Elasticsearch\Asset;

use Akeneo\AssetManager\Application\Asset\Subscribers\IndexByAssetFamilyInBackgroundInterface;
use Akeneo\AssetManager\Domain\Event\AssetCreatedEvent;
use Akeneo\AssetManager\Domain\Event\AssetUpdatedEvent;
use Akeneo\AssetManager\Domain\Event\AttributeDeletedEvent;
use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
use Akeneo\AssetManager\Domain\Repository\AssetIndexerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class IndexAssetEventAggregator implements EventAggregatorInterface
{
    // Twice the size of the API batch size to be able to have both creation and edition events
    private const MAX_ASSET_TO_INDEX_BATCH = 200;

    /** @var AssetIdentifier[] */
    private array $assetsToIndex = [];

    private AssetIndexerInterface $assetIndexer;

    private IndexByAssetFamilyInBackgroundInterface $indexByAssetFamilyInBackground;

    public function __construct(
        AssetIndexerInterface $assetIndexer,
        IndexByAssetFamilyInBackgroundInterface $indexByAssetFamilyInBackground
    ) {
        $this->assetIndexer = $assetIndexer;
        $this->indexByAssetFamilyInBackground = $indexByAssetFamilyInBackground;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            AssetUpdatedEvent::class     => 'whenAssetUpdated',
            AssetCreatedEvent::class     => 'whenAssetCreated',
            AttributeDeletedEvent::class => 'whenAttributeIsDeleted',
        ];
    }

    public function whenAssetUpdated(AssetUpdatedEvent $assetUpdatedEvent): void
    {
        $this->assetsToIndex[] = $assetUpdatedEvent->getAssetIdentifier();

        if (count($this->assetsToIndex) === self::MAX_ASSET_TO_INDEX_BATCH) {
            $this->assetIndexer->indexByAssetIdentifiers($this->assetsToIndex);
            $this->assetIndexer->refresh();
            $this->assetsToIndex = [];
        }
    }

    public function whenAssetCreated(AssetCreatedEvent $assetCreatedEvent): void
    {
        $this->assetsToIndex[] = $assetCreatedEvent->getAssetIdentifier();

        if (count($this->assetsToIndex) === self::MAX_ASSET_TO_INDEX_BATCH) {
            $this->assetIndexer->indexByAssetIdentifiers($this->assetsToIndex);
            $this->assetIndexer->refresh();
            $this->assetsToIndex = [];
        }
    }

    public function whenAttributeIsDeleted(AttributeDeletedEvent $attributeDeletedEvent): void
    {
        $this->indexByAssetFamilyInBackground->execute($attributeDeletedEvent->assetFamilyIdentifier);
    }

    public function flushEvents(): void
    {
        if (empty($this->assetsToIndex)) {
            return;
        }

        $this->assetIndexer->indexByAssetIdentifiers($this->assetsToIndex);
        $this->assetIndexer->refresh();
    }
}
