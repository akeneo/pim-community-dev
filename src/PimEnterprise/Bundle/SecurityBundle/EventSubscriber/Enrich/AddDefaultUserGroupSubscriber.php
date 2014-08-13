<?php

namespace PimEnterprise\Bundle\SecurityBundle\EventSubscriber\Enrich;

use Pim\Bundle\CatalogBundle\Entity\AttributeGroup;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;
use Pim\Bundle\EnrichBundle\Event\AttributeGroupEvents;
use Pim\Bundle\EnrichBundle\Event\CategoryEvents;
use Pim\Bundle\UserBundle\Entity\Repository\GroupRepository;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use PimEnterprise\Bundle\SecurityBundle\Manager\AttributeGroupAccessManager;
use PimEnterprise\Bundle\SecurityBundle\Manager\CategoryAccessManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Add the default user group to the entity of the generic event
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class AddDefaultUserGroupSubscriber implements EventSubscriberInterface
{
    /** @var GroupRepository */
    protected $groupRepository;

    /** @var CategoryAccessManager */
    protected $catAccessManager;

    /**
     * @param GroupRepository       $groupRepository
     * @param CategoryAccessManager $catAccessManager
     */
    public function __construct(
        GroupRepository $groupRepository,
        CategoryAccessManager $catAccessManager,
        AttributeGroupAccessManager $attGrpAccessManager
    ) {
        $this->groupRepository     = $groupRepository;
        $this->catAccessManager    = $catAccessManager;
        $this->attGrpAccessManager = $attGrpAccessManager;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            CategoryEvents::POST_CREATE       => 'addDefaultUserGroupForTree',
            AttributeGroupEvents::POST_CREATE => 'addDefaultUserGroupForAttributeGroup'
        ];
    }

    /**
     * Add default user group for tree
     *
     * @param GenericEvent $event
     */
    public function addDefaultUserGroupForTree(GenericEvent $event)
    {
        $object = $event->getSubject();
        if ($object instanceof CategoryInterface && $object->isRoot()) {
            $userGroup = $this->groupRepository->getDefaultUserGroup();
            $this->catAccessManager->grantAccess($object, $userGroup, Attributes::OWN_PRODUCTS, true);
        }
    }

    /**
     * Add default user group for attribute group
     *
     * @param GenericEvent $event
     */
    public function addDefaultUserGroupForAttributeGroup(GenericEvent $event)
    {
        $object = $event->getSubject();
        if ($object instanceof AttributeGroup) {
            $userGroup = $this->groupRepository->getDefaultUserGroup();
            $this->attGrpAccessManager->grantAccess($object, $userGroup, Attributes::EDIT_ATTRIBUTES, true);
        }
    }

    /**
     * Get the default user group
     *
     * @return \Oro\Bundle\UserBundle\Entity\Group
     */
    protected function getDefaultUserGroup()
    {
        return $this->groupRepository->getDefaultUserGroup();
    }
}
