<?php

namespace PimEnterprise\Bundle\WorkflowBundle\EventSubscriber\PublishedProduct;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Pim\Bundle\CatalogBundle\Event;
use Pim\Bundle\CatalogBundle\Entity\Repository\CategoryRepository;
use PimEnterprise\Bundle\WorkflowBundle\Exception\PublishedProductConsistencyException;
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

    /** @var \Entity\Repository\CategoryRepository */
    protected $categoryRepository;

    /**
     * @param PublishedProductRepositoryInterface $publishedRepository
     * @param CategoryRepository                  $categoryRepository
     */
    public function __construct(
        PublishedProductRepositoryInterface $publishedRepository,
        CategoryRepository $categoryRepository
    ) {
        $this->publishedRepository = $publishedRepository;
        $this->categoryRepository  = $categoryRepository;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            Event\ProductEvents::PRE_REMOVE           => 'checkProductHasBeenPublished',
            Event\FamilyEvents::PRE_REMOVE            => 'checkFamilyLinkedToPublishedProduct',
            Event\AttributeEvents::PRE_REMOVE         => 'checkAttributeLinkedToPublishedProduct',
            Event\CategoryEvents::PRE_REMOVE_CATEGORY => 'checkCategoryLinkedToPublishedProduct',
            Event\CategoryEvents::PRE_REMOVE_TREE     => 'checkCategoryLinkedToPublishedProduct',
            Event\AssociationTypeEvents::PRE_REMOVE   => 'checkAssociationTypeLinkedToPublishedProduct',
            Event\GroupEvents::PRE_REMOVE             => 'checkGroupLinkedToPublishedProduct'
        ];
    }

    /**
     * Check if the product is published
     *
     * @param GenericEvent $event
     *
     * @throws PublishedProductConsistencyException
     */
    public function checkProductHasBeenPublished(GenericEvent $event)
    {
        $product   = $event->getSubject();
        $published = $this->publishedRepository->findOneByOriginalProduct($product);

        if ($published) {
            throw new PublishedProductConsistencyException('Impossible to remove a published product');
        }
    }

    /**
     * Check if the family is linked to a published product
     *
     * @param GenericEvent $event
     *
     * @throws PublishedProductConsistencyException
     */
    public function checkFamilyLinkedToPublishedProduct(GenericEvent $event)
    {
        $family = $event->getSubject();
        $publishedCount = $this->publishedRepository->countPublishedProductsForFamily($family);

        if ($publishedCount > 0) {
            throw new PublishedProductConsistencyException(
                'Impossible to remove family linked to a published product'
            );
        }
    }

    /**
     * Check if the category is linked to a published product
     *
     * @param GenericEvent $event
     *
     * @throws PublishedProductConsistencyException
     */
    public function checkCategoryLinkedToPublishedProduct(GenericEvent $event)
    {
        $category = $event->getSubject();
        $categoryIds = $this->categoryRepository->getAllChildrenIds($category);
        $categoryIds[] = $category->getId();

        $publishedCount = $this->publishedRepository->countPublishedProductsForCategoryAndChildren($categoryIds);

        if ($publishedCount > 0) {
            throw new PublishedProductConsistencyException(
                'Impossible to remove category linked to a published product'
            );
        }
    }

    /**
     * Check if the attribute is linked to a published product
     *
     * @param GenericEvent $event
     *
     * @throws PublishedProductConsistencyException
     */
    public function checkAttributeLinkedToPublishedProduct(GenericEvent $event)
    {
        $attribute = $event->getSubject();
        $publishedCount = $this->publishedRepository->countPublishedProductsForAttribute($attribute);

        if ($publishedCount > 0) {
            throw new PublishedProductConsistencyException(
                'Impossible to remove attribute linked to a published product'
            );
        }
    }

    /**
     * Check if the group is linked to a published product
     *
     * @param GenericEvent $event
     *
     * @throws PublishedProductConsistencyException
     */
    public function checkGroupLinkedToPublishedProduct(GenericEvent $event)
    {
        $group = $event->getSubject();
        $publishedCount = $this->publishedRepository->countPublishedProductsForGroup($group);

        if ($publishedCount > 0) {
            throw new PublishedProductConsistencyException(
                'Impossible to remove group linked to a published product'
            );
        }
    }

    /**
     * Check if the association type is linked to a published product
     *
     * @param GenericEvent $event
     *
     * @throws PublishedProductConsistencyException
     */
    public function checkAssociationTypeLinkedToPublishedProduct(GenericEvent $event)
    {
        $associationType = $event->getSubject();
        $publishedCount = $this->publishedRepository->countPublishedProductsForAssociationType($associationType);

        if ($publishedCount > 0) {
            throw new PublishedProductConsistencyException(
                'Impossible to remove association type linked to a published product'
            );
        }
    }
}
