<?php

namespace PimEnterprise\Bundle\WorkflowBundle\EventSubscriber\PublishedProduct;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Pim\Bundle\CatalogBundle\CatalogEvents;
use PimEnterprise\Bundle\WorkflowBundle\Repository\PublishedProductRepositoryInterface;

/**
 * Check some pre remove events and forbid deletion if the entity is linked to a published product
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class CheckPublishedProductOnRemovalSubscriber implements EventSubscriberInterface
{
    /** @var PublishedProductRepositoryInterface */
    protected $publishedRepository;

    /**
     * @param PublishedProductRepositoryInterface $publishedRepository
     */
    public function __construct(PublishedProductRepositoryInterface $publishedRepository)
    {
        $this->publishedRepository = $publishedRepository;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            CatalogEvents::PRE_REMOVE_PRODUCT   => 'checkProductHasBeenPublished',
            CatalogEvents::PRE_REMOVE_FAMILY    => 'checkFamilyLinkedToPublishedProduct',
            CatalogEvents::PRE_REMOVE_ATTRIBUTE => 'checkAttributeLinkedToPublishedProduct',
            CatalogEvents::PRE_REMOVE_CATEGORY  => 'checkCategoryLinkedToPublishedProduct'
        ];
    }

    /**
     * Check if the product is published
     *
     * @param GenericEvent $event
     *
     * @throws ConflictHttpException
     */
    public function checkProductHasBeenPublished(GenericEvent $event)
    {
        $product   = $event->getSubject();
        $published = $this->publishedRepository->findOneByOriginalProductId($product->getId());

        if ($published) {
            throw new ConflictHttpException('Impossible to remove a published product');
        }
    }

    /**
     * Check if the family is linked to a published product
     *
     * @param GenericEvent $event
     *
     * @throws ConflictHttpException
     */
    public function checkFamilyLinkedToPublishedProduct(GenericEvent $event)
    {
        $family = $event->getSubject();
        $publishedCount = $this->publishedRepository->countPublishedProductsForFamily($family);

        if ($publishedCount > 0) {
            throw new ConflictHttpException('Impossible to remove family linked to a published product');
        }
    }

    /**
     * Check if the category is linked to a published product
     *
     * @param GenericEvent $event
     *
     * @throws ConflictHttpException
     */
    public function checkCategoryLinkedToPublishedProduct(GenericEvent $event)
    {
        $category = $event->getSubject();
        $publishedCount = $this->publishedRepository->countPublishedProductsForCategory($category);

        if ($publishedCount > 0) {
            throw new ConflictHttpException('Impossible to remove category linked to a published product');
        }
    }

    /**
     * Check if the attribute is linked to a published product
     *
     * @param GenericEvent $event
     *
     * @throws ConflictHttpException
     */
    public function checkAttributeLinkedToPublishedProduct(GenericEvent $event)
    {
        $attribute = $event->getSubject();
        $publishedCount = $this->publishedRepository->countPublishedProductsForAttribute($attribute);

        if ($publishedCount > 0) {
            throw new ConflictHttpException('Impossible to remove attribute linked to a published product');
        }
    }

    /**
     * Check if the group is linked to a published product
     *
     * @param GenericEvent $event
     *
     * @throws ConflictHttpException
     */
    public function checkGroupLinkedToPublishedProduct(GenericEvent $event)
    {
        $group = $event->getSubject();
        $publishedCount = $this->publishedRepository->countPublishedProductsForGroup($group);

        if ($publishedCount > 0) {
            throw new ConflictHttpException('Impossible to remove group linked to a published product');
        }
    }

    /**
     * Check if the association type is linked to a published product
     *
     * @param GenericEvent $event
     *
     * @throws ConflictHttpException
     */
    public function checkAssociationTypeToPublishedProduct(GenericEvent $event)
    {
        $associationType = $event->getSubject();
        $publishedCount = $this->publishedRepository->countPublishedProductsForAssociationType($associationType);
        throw new \Exception('Not yet implemented');
    }
}
