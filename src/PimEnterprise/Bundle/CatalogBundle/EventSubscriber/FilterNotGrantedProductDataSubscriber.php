<?php

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
use Pim\Component\Catalog\Model\ProductInterface;
use PimEnterprise\Component\Workflow\Model\PublishedProductInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Filter not granted categories and products associations from product.
 *
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
class FilterNotGrantedProductDataSubscriber implements EventSubscriber
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
        $product = $event->getObject();
        if (!$product instanceof ProductInterface) {
            return;
        }

        $associatedProductFilter = $product instanceof PublishedProductInterface ?
            $this->container->get('pimee_catalog.security.filter.not_granted_associated_published_product') :
            $this->container->get('pimee_catalog.security.filter.not_granted_associated_product');

        $associatedProductFilter->filter($product);

        if (0 !== $product->getCategories()->count()) {
            $this->container->get('pimee_catalog.security.filter.not_granted_category')->filter($product);
        }
    }
}
