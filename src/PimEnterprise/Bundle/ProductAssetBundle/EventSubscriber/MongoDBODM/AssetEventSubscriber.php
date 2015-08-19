<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ProductAssetBundle\EventSubscriber\MongoDBODM;

use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use PimEnterprise\Bundle\ProductAssetBundle\Event\AssetEvent;
use PimEnterprise\Component\ProductAsset\Repository\ProductCascadeRemovalRepositoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Asset event subscriber for MongoDBODM
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class AssetEventSubscriber implements EventSubscriberInterface
{
    /** @var ProductCascadeRemovalRepositoryInterface  */
    protected $cascadeRemovalRepository;

    /** @var AttributeRepositoryInterface  */
    protected $attributeRepository;

    /**
     * @param ProductCascadeRemovalRepositoryInterface $cascadeRemovalRepository
     * @param AttributeRepositoryInterface             $attributeRepository
     */
    public function __construct(
        ProductCascadeRemovalRepositoryInterface $cascadeRemovalRepository,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->cascadeRemovalRepository = $cascadeRemovalRepository;
        $this->attributeRepository      = $attributeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            AssetEvent::POST_REMOVE => 'cascadeAssetRemove'
        ];
    }

    /**
     * Updates product document when an asset related to a product is removed
     * Triggered by AssetEvent::POST_REMOVE
     *
     * @param GenericEvent $event
     *
     * @return AssetEvent
     */
    public function cascadeAssetRemove(GenericEvent $event)
    {
        $asset = $event->getSubject();
        $attributeCodes = $this->attributeRepository->getAttributeCodesByType('pim_assets_collection');
        $this->cascadeRemovalRepository->cascadeAssetRemoval($asset, $attributeCodes);

        return $event;
    }
}
