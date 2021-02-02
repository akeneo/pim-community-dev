<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Application\Asset\Subscribers;

use Akeneo\AssetManager\Domain\Event\AssetDeletedEvent;
use Akeneo\AssetManager\Domain\Event\AssetsDeletedEvent;
use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
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
            AssetsDeletedEvent::class => 'whenMultipleAssetsDeleted',
        ];
    }

    public function whenAssetDeleted(AssetDeletedEvent $assetDeletedEvent): void
    {
        $this->assetIndexer->removeAssetByAssetFamilyIdentifierAndCode(
            (string) $assetDeletedEvent->getAssetFamilyIdentifier(),
            (string) $assetDeletedEvent->getAssetCode()
        );
    }

    public function whenMultipleAssetsDeleted(AssetsDeletedEvent $assetsDeletedEvent): void
    {
        $normalizedAssetCode = array_map(
            fn(AssetCode $assetCode) => $assetCode->normalize(),
            $assetsDeletedEvent->getAssetCodes()
        );

        $this->assetIndexer->removeAssetByAssetFamilyIdentifierAndCodes(
            (string) $assetsDeletedEvent->getAssetFamilyIdentifier(),
            $normalizedAssetCode
        );
    }
}
