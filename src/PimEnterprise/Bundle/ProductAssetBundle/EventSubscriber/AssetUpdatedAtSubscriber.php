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

namespace PimEnterprise\Bundle\ProductAssetBundle\EventSubscriber;

use Akeneo\Component\StorageUtils\StorageEvents;
use Doctrine\ORM\EntityManagerInterface;
use PimEnterprise\Component\ProductAsset\Model\ReferenceInterface;
use PimEnterprise\Component\ProductAsset\Model\VariationInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Update the asset "updatedAt" field whenever the asset reference(s)
 * or variations are updated.
 *
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class AssetUpdatedAtSubscriber implements EventSubscriberInterface
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            StorageEvents::PRE_SAVE => 'updateAsset',
        ];
    }

    /**
     * @param GenericEvent $event
     */
    public function updateAsset(GenericEvent $event): void
    {
        $entity = $event->getSubject();

        if (!($entity instanceof ReferenceInterface || $entity instanceof VariationInterface)) {
            return;
        }

        $asset = $entity->getAsset();
        $asset->setUpdatedAt(new \DateTime());
        $this->entityManager->persist($asset);
    }
}
