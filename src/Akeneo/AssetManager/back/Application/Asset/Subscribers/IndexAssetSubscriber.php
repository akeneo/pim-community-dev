<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Application\Asset\Subscribers;

use Akeneo\AssetManager\Domain\Event\AttributeDeletedEvent;
use Akeneo\AssetManager\Domain\Event\AssetUpdatedEvent;
use Akeneo\AssetManager\Domain\Repository\AssetIndexerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class IndexAssetSubscriber implements EventSubscriberInterface
{
    /** @var AssetIndexerInterface */
    private $assetIndexer;

    /** @var IndexByAssetFamilyInBackgroundInterface */
    private $indexByAssetFamilyInBackground;

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
            AssetUpdatedEvent::class    => 'whenAssetUpdated',
            AttributeDeletedEvent::class => 'whenAttributeIsDeleted',
        ];
    }

    public function whenAssetUpdated(AssetUpdatedEvent $assetUpdatedEvent): void
    {
        $this->assetIndexer->index($assetUpdatedEvent->getAssetIdentifier());
    }

    public function whenAttributeIsDeleted(AttributeDeletedEvent $attributeDeletedEvent): void
    {
        $this->indexByAssetFamilyInBackground->execute($attributeDeletedEvent->assetFamilyIdentifier);
    }
}
