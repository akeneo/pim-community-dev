<?php

namespace Pim\Bundle\CatalogBundle\EventSubscriber\MongoDBODM;

use Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\ProductRepository;
use Pim\Bundle\CatalogBundle\Entity\Repository\AssociationTypeRepository;
use Pim\Bundle\CatalogBundle\Event\ProductEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Remove associated product on product remove
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RemoveOutdatedProductsFromAssociationsSubscriber implements EventSubscriberInterface
{
    /** @var ProductRepository */
    protected $productRepository;

    /** @var AssociationTypeRepository */
    protected $assocTypeRepository;

    /**
     * @param ProductRepository         $productRepository
     * @param AssociationTypeRepository $assocTypeRepository
     */
    public function __construct(
        ProductRepository $productRepository,
        AssociationTypeRepository $assocTypeRepository
    ) {
        $this->productRepository   = $productRepository;
        $this->assocTypeRepository = $assocTypeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            ProductEvents::POST_REMOVE => 'removeAssociatedProduct'
        ];
    }

    /**
     * @param GenericEvent $event
     */
    public function removeAssociatedProduct(GenericEvent $event)
    {
        /** @var \Pim\Bundle\CatalogBundle\Model\ProductInterface $product */
        $product = $event->getSubject();
        $assocTypeCount = $this->assocTypeRepository->countAll();

        $this->productRepository->removeAssociatedProduct($product, $assocTypeCount);
    }
}
