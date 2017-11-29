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

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Filter not granted categories and products associations from product model.
 *
 * @author Adrien Pétremann <adrien.petremann@akeneo.com>
 */
class FilterNotGrantedProductModelDataSubscriber implements EventSubscriber
{
    /** @var ContainerInterface */
    private $container;

    /**
     * The container is injected here to avoid a circular reference
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return [Events::postLoad];
    }

    /**
     * @param LifecycleEventArgs $event
     */
    public function postLoad(LifecycleEventArgs $event)
    {
        $productModel = $event->getObject();
        if (!$productModel instanceof ProductModelInterface) {
            return;
        }

        if (0 !== $productModel->getCategories()->count()) {
            $this->container->get('pimee_catalog.security.filter.not_granted_category')->filter($productModel);
        }
    }
}
