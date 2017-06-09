<?php

namespace PimEnterprise\Bundle\CatalogBundle\EventSubscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Pim\Component\Catalog\Model\ProductInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Filter not granted categories and products associations from product.
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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

        $this->container->get('pimee_catalog.security.filter.not_granted_associated_product')->filter($product);

        if (0 !== $product->getCategories()->count()) {
            $this->container->get('pimee_catalog.security.filter.not_granted_category')->filter($product);
        }
    }
}
