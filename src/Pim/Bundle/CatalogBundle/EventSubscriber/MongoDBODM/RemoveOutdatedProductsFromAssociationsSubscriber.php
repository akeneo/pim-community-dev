<?php

namespace Pim\Bundle\CatalogBundle\EventSubscriber\MongoDBODM;

use Akeneo\Component\StorageUtils\Event\RemoveEvent;
use Akeneo\Component\StorageUtils\StorageEvents;
use Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\ProductRepositoryInterface;
use Pim\Bundle\CatalogBundle\Event\ProductEvents;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Repository\AssociationTypeRepositoryInterface;
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
    /** @var ProductRepositoryInterface */
    protected $productRepository;

    /** @var AssociationTypeRepositoryInterface */
    protected $assocTypeRepository;

    /**
     * @param ProductRepositoryInterface         $productRepository
     * @param AssociationTypeRepositoryInterface $assocTypeRepository
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        AssociationTypeRepositoryInterface $assocTypeRepository
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
            StorageEvents::POST_REMOVE      => 'removeAssociatedProduct',
            ProductEvents::POST_MASS_REMOVE => 'removeAssociatedProducts'
        ];
    }

    /**
     * Remove associated product from a single product
     *
     * @param RemoveEvent $event
     */
    public function removeAssociatedProduct(RemoveEvent $event)
    {
        $subject = $event->getSubject();

        if (!$subject instanceof ProductInterface) {
            return;
        }

        $assocTypeCount = $this->assocTypeRepository->countAll();

        $this->productRepository->removeAssociatedProduct($event->getSubjectId(), $assocTypeCount);
    }

    /**
     * Remove associated products from a list of product ids
     *
     * @param GenericEvent $event
     */
    public function removeAssociatedProducts(GenericEvent $event)
    {
        $productIds = $event->getSubject();
        $assocTypeCount = $this->assocTypeRepository->countAll();

        foreach ($productIds as $productId) {
            $this->productRepository->removeAssociatedProduct($productId, $assocTypeCount);
        }
    }
}
