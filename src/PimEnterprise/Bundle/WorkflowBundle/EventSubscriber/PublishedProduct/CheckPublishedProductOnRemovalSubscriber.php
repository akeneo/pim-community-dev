<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\EventSubscriber\PublishedProduct;

use Akeneo\Component\StorageUtils\StorageEvents;
use Doctrine\Common\Util\ClassUtils;
use Pim\Bundle\CatalogBundle\Model\AssociationTypeInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeOptionInterface;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;
use Pim\Bundle\CatalogBundle\Model\FamilyInterface;
use Pim\Bundle\CatalogBundle\Model\GroupInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use PimEnterprise\Bundle\WorkflowBundle\Exception\PublishedProductConsistencyException;
use PimEnterprise\Bundle\WorkflowBundle\Repository\PublishedProductRepositoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Check some pre remove events and forbid deletion if the entity is linked to a published product
 *
 * @author Romain Monceau <romain@akeneo.com>
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
            StorageEvents::PRE_REMOVE => 'preRemove',
        ];
    }

    /**
     * Check if the family is linked to a published product
     *
     * @param GenericEvent $event
     *
     * @throws PublishedProductConsistencyException
     */
    public function preRemove(GenericEvent $event)
    {
        $subject = $event->getSubject();

        if (!$this->isSubjectRelatedToPublished($subject)) {
            return;
        }

        $message = 'Impossible to remove a published product';

        if (!$subject instanceof ProductInterface) {
            $classname = array_slice(explode('\\', ClassUtils::getClass($subject)), -1)[0];
            $message = sprintf(
                'Impossible to remove %s linked to a published product',
                strtolower(preg_replace('/([a-z])([A-Z])/', '$1 $2', $classname, -1))
            );
        }

        throw new PublishedProductConsistencyException($message);
    }

    /**
     * @param mixed $subject
     *
     * @return bool
     */
    private function isSubjectRelatedToPublished($subject)
    {
        if (!is_object($subject)) {
            return false;
        }

        if ($subject instanceof FamilyInterface) {
            return $this->publishedRepository->countPublishedProductsForFamily($subject) > 0;
        }

        if ($subject instanceof GroupInterface) {
            return $this->publishedRepository->countPublishedProductsForGroup($subject) > 0;
        }

        if ($subject instanceof AssociationTypeInterface) {
            return $this->publishedRepository->countPublishedProductsForAssociationType($subject) > 0;
        }

        if ($subject instanceof CategoryInterface) {
            return $this->publishedRepository->countPublishedProductsForCategory($subject) > 0;
        }

        if ($subject instanceof AttributeInterface) {
            return $this->publishedRepository->countPublishedProductsForAttribute($subject) > 0;
        }

        if ($subject instanceof AttributeOptionInterface) {
            return $this->publishedRepository->countPublishedProductsForAttributeOption($subject) > 0;
        }

        if ($subject instanceof ProductInterface) {
            return null !== $this->publishedRepository->findOneByOriginalProduct($subject);
        }

        return false;
    }
}
