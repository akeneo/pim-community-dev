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

use Akeneo\Component\StorageUtils\StorageEvents;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use PimEnterprise\Bundle\ProductAssetBundle\AttributeType\AttributeTypes;
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
    protected $cascadeRemovalRepo;

    /** @var AttributeRepositoryInterface  */
    protected $attributeRepository;

    /**
     * @param ProductCascadeRemovalRepositoryInterface $cascadeRemovalRepo
     * @param AttributeRepositoryInterface             $attributeRepository
     */
    public function __construct(
        ProductCascadeRemovalRepositoryInterface $cascadeRemovalRepo,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->cascadeRemovalRepo  = $cascadeRemovalRepo;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            AssetEvent::PRE_REMOVE => 'cascadeAssetRemove'
        ];
    }

    /**
     * Updates product document when an asset related to a product is removed
     * Triggered by AssetEvent::POST_REMOVE
     *
     * @param GenericEvent $event
     *
     * @return GenericEvent
     */
    public function cascadeAssetRemove(GenericEvent $event)
    {
        $asset = $event->getSubject();
        $attributeCodes = $this->attributeRepository->getAttributeCodesByType(AttributeTypes::ASSETS_COLLECTION);
        $this->cascadeRemovalRepo->cascadeAssetRemoval($asset, $attributeCodes);

        return $event;
    }
}
