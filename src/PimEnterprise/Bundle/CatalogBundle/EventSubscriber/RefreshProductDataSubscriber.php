<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\CatalogBundle\EventSubscriber;

use Akeneo\Component\StorageUtils\StorageEvents;
use Doctrine\ORM\EntityManagerInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * This is an ugly fix to be able to fix problem when data are saved.
 * Currently, on a PRE_SAVE event, not granted data are merged with user's data.
 * Problem, once your product is saved and if you still work on your product after that,
 * your product will contain not granted data and you won't be able to save it anymore.
 *
 * Add a refresh fix this problem but this subscriber will be trash after the rework on save.
 *
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
class RefreshProductDataSubscriber implements EventSubscriberInterface
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
    public static function getSubscribedEvents()
    {
        return [
            StorageEvents::POST_SAVE     => ['refreshProduct', 250],
            StorageEvents::POST_SAVE_ALL => ['refreshProducts', 250],
        ];
    }

    /**
     * @param GenericEvent $event
     */
    public function refreshProduct(GenericEvent $event): void
    {
        $product = $event->getSubject();

        if (!$product instanceof ProductInterface) {
            return;
        }

        if (!$event->hasArgument('unitary') || false === $event->getArgument('unitary')) {
            return;
        }

        $this->entityManager->refresh($product);
    }

    /**
     * @param GenericEvent $event
     */
    public function refreshProducts(GenericEvent $event): void
    {
        $products = $event->getSubject();

        if (!is_array($products)) {
            return;
        }

        if (!current($products) instanceof ProductInterface) {
            return;
        }

        foreach ($products as $product) {
            $this->entityManager->refresh($product);
        }
    }
}
