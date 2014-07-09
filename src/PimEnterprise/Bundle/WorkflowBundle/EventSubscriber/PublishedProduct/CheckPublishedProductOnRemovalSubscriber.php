<?php

namespace PimEnterprise\Bundle\WorkflowBundle\EventSubscriber\PublishedProduct;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Pim\Bundle\CatalogBundle\CatalogEvents;
use Pim\Bundle\CatalogBundle\Entity\Repository\CategoryRepository;
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
            CatalogEvents::PRE_REMOVE_PRODUCT          => 'checkProductHasBeenPublished',
            CatalogEvents::PRE_REMOVE_FAMILY           => 'checkFamilyLinkedToPublishedProduct',
            CatalogEvents::PRE_REMOVE_ATTRIBUTE        => 'checkAttributeLinkedToPublishedProduct',
            CatalogEvents::PRE_REMOVE_CATEGORY         => 'checkCategoryLinkedToPublishedProduct',
            CatalogEvents::PRE_REMOVE_ASSOCIATION_TYPE => 'checkAssociationTypeLinkedToPublishedProduct',
            CatalogEvents::PRE_REMOVE_GROUP            => 'checkGroupLinkedToPublishedProduct'
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
            $this->throwConflictException('Impossible to remove a published product');
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
            $this->throwConflictException('Impossible to remove family linked to a published product');
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
        $categoryIds = $this->categoryRepository->getAllChildrenIds($category);
        $categoryIds += $category->getId();

        $publishedCount = $this->publishedRepository->countPublishedProductsForCategoryAndChildren($categoryIds);

        if ($publishedCount > 0) {
            $this->throwConflictException('Impossible to remove category linked to a published product');
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
            $this->throwConflictException('Impossible to remove attribute linked to a published product');
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
            $this->throwConflictException('Impossible to remove group linked to a published product');
        }
    }

    /**
     * Check if the association type is linked to a published product
     *
     * @param GenericEvent $event
     *
     * @throws ConflictHttpException
     */
    public function checkAssociationTypeLinkedToPublishedProduct(GenericEvent $event)
    {
        $associationType = $event->getSubject();
        $publishedCount = $this->publishedRepository->countPublishedProductsForAssociationType($associationType);

        if ($publishedCount > 0) {
            $this->throwConflictException('Impossible to remove association type linked to a published product');
        }
    }

    /**
     * Create a conflict http exception
     *
     * @param $message
     *
     * @throws \Symfony\Component\HttpKernel\Exception\ConflictHttpException
     */
    protected function throwConflictException($message)
    {
        throw new ConflictHttpException($message);
    }
}
