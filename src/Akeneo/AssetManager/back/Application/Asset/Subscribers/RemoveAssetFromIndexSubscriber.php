<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Application\Asset\Subscribers;

use Akeneo\AssetManager\Domain\Event\AssetDeletedEvent;
use Akeneo\AssetManager\Domain\Event\AssetFamilyAssetsDeletedEvent;
use Akeneo\AssetManager\Domain\Repository\AssetIndexerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Listen to deleted assets events in order to remove them from the search engine index.
 *
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class RemoveAssetFromIndexSubscriber implements EventSubscriberInterface
{
    /** @var AssetIndexerInterface */
    private $assetIndexer;

    public function __construct(AssetIndexerInterface $assetIndexer)
    {
        $this->assetIndexer = $assetIndexer;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            AssetDeletedEvent::class => 'whenAssetDeleted',
            AssetFamilyAssetsDeletedEvent::class => 'whenAllAssetsDeleted',
        ];
    }

    public function whenAssetDeleted(AssetDeletedEvent $assetDeletedEvent): void
    {
        $this->assetIndexer->removeAssetByAssetFamilyIdentifierAndCode(
            (string) $assetDeletedEvent->getAssetFamilyIdentifier(),
            (string) $assetDeletedEvent->getAssetCode()
        );
    }

    public function whenAllAssetsDeleted(AssetFamilyAssetsDeletedEvent $assetDeletedEvent): void
    {
        $this->assetIndexer->removeByAssetFamilyIdentifier(
            (string) $assetDeletedEvent->getAssetFamilyIdentifier()
        );
    }
}
