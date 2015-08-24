<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ProductAssetBundle\Event;

use PimEnterprise\Component\ProductAsset\Finder\AssetFinderInterface;
use PimEnterprise\Component\ProductAsset\VariationsCollectionFilesGeneratorInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Asset events listenener
 *
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
class MissingVariationsEventSubscriber implements EventSubscriberInterface
{
    /** @var VariationsCollectionFilesGeneratorInterface */
    protected $generator;

    /** @var AssetFinderInterface */
    protected $finder;

    /**
     * @param VariationsCollectionFilesGeneratorInterface $generator
     * @param AssetFinderInterface                        $finder
     */
    public function __construct(VariationsCollectionFilesGeneratorInterface $generator, AssetFinderInterface $finder)
    {
        $this->generator = $generator;
        $this->finder    = $finder;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            AssetEvent::POST_UPLOAD_FILES => 'onAssetFilesUploaded'
        ];
    }

    /**
     * Generate missing variations for one asset or for all assets
     * Triggered by AssetEvent::POST_UPLOAD_FILES
     *
     * @param AssetEvent $event
     *
     * @return AssetEvent
     */
    public function onAssetFilesUploaded(AssetEvent $event)
    {
        $variations = $this->finder->retrieveVariationsNotGenerated($event->getSubject());
        $processed  = $this->generator->generate($variations, true);

        $event->setProcessedList($processed);

        return $event;
    }
}
