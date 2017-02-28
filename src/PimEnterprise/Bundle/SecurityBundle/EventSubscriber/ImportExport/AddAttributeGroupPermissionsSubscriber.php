<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\SecurityBundle\EventSubscriber\ImportExport;

use Akeneo\Component\StorageUtils\StorageEvents;
use Pim\Bundle\UserBundle\Doctrine\ORM\Repository\GroupRepository;
use Pim\Component\Catalog\Model\AttributeGroupInterface;
use PimEnterprise\Bundle\SecurityBundle\Manager\AttributeGroupAccessManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Adds the default permissions when an attribute group is created by an import.
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class AddAttributeGroupPermissionsSubscriber implements EventSubscriberInterface
{
    /** @var AttributeGroupAccessManager */
    protected $accessManager;

    /** @var array */
    protected $newAttributeGroupCodes;

    /** @var GroupRepository */
    protected $groupRepository;

    /**
     * @param AttributeGroupAccessManager $accessManager
     * @param GroupRepository             $groupRepository
     */
    public function __construct(
        AttributeGroupAccessManager $accessManager,
        GroupRepository $groupRepository
    ) {
        $this->accessManager = $accessManager;
        $this->groupRepository = $groupRepository;
        $this->newAttributeGroupCodes = [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            StorageEvents::PRE_SAVE_ALL  => 'storeNewAttributeGroupCodes',
            StorageEvents::POST_SAVE_ALL => 'setDefaultPermissions'
        ];
    }

    /**
     * Store locally the new attribute group codes
     *
     * @param GenericEvent $event
     */
    public function storeNewAttributeGroupCodes(GenericEvent $event)
    {
        $attributeGroups = $event->getSubject();
        foreach ($attributeGroups as $attributeGroup) {
            if ($attributeGroup instanceof AttributeGroupInterface && null === $attributeGroup->getId()) {
                $this->newAttributeGroupCodes[] = $attributeGroup->getCode();
            }
        }
    }

    /**
     * Set the default permissions to the new attribute group
     *
     * @param GenericEvent $event
     */
    public function setDefaultPermissions(GenericEvent $event)
    {
        $defaultGroup = $this->groupRepository->getDefaultUserGroup();
        $attributeGroups = $event->getSubject();
        foreach ($attributeGroups as $attributeGroup) {
            if ($attributeGroup instanceof AttributeGroupInterface &&
                in_array($attributeGroup->getCode(), $this->newAttributeGroupCodes)) {
                $this->accessManager->setAccess($attributeGroup, [$defaultGroup], [$defaultGroup]);
                unset($this->newAttributeGroupCodes[$attributeGroup->getCode()]);
            }
        }
    }
}
