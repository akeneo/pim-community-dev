<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\EnrichBundle\EventSubscriber;

use Akeneo\Component\Batch\Model\JobInstance;
use Doctrine\ORM\EntityRepository;
use Pim\Bundle\EnrichBundle\Event\AttributeGroupEvents;
use Pim\Bundle\EnrichBundle\Event\JobInstanceEvents;
use Pim\Component\Catalog\Model\AttributeGroupInterface;
use Pim\Component\User\Model\GroupInterface;
use PimEnterprise\Bundle\SecurityBundle\Manager\AttributeGroupAccessManager;
use PimEnterprise\Bundle\SecurityBundle\Manager\JobProfileAccessManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Subscriber responsible for saving permissions on attribute groups and job instances.
 * Ideally it would also be used for categories and locales once the forms will be reworked.
 *
 * This logic has been moved here since the permissions saving must be done at very end, once the entities have been
 * updated, validated and saved. Unfortunately we can't use events from the saver layer since permissions are not
 * embedded in the entities.
 *
 * @author Yohan Blain <yohan.blain@akeneo.com>
 */
class SavePermissionsSubscriber implements EventSubscriberInterface
{
    /** @var EntityRepository */
    private $userGroupRepository;

    /** @var AttributeGroupAccessManager */
    private $attributeGroupAccessManager;

    /** @var JobProfileAccessManager */
    private $jobInstanceAccessManager;

    /**
     * @param EntityRepository            $userGroupRepository
     * @param AttributeGroupAccessManager $attributeGroupAccessManager
     * @param JobProfileAccessManager     $jobInstanceAccessManager
     */
    public function __construct(
        EntityRepository $userGroupRepository,
        AttributeGroupAccessManager $attributeGroupAccessManager,
        JobProfileAccessManager $jobInstanceAccessManager
    ) {
        $this->userGroupRepository = $userGroupRepository;
        $this->attributeGroupAccessManager = $attributeGroupAccessManager;
        $this->jobInstanceAccessManager = $jobInstanceAccessManager;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            AttributeGroupEvents::POST_SAVE => 'saveAttributeGroupPermissions',
            JobInstanceEvents::POST_SAVE    => 'saveJobInstancePermissions',
        ];
    }

    /**
     * @param GenericEvent $event
     */
    public function saveAttributeGroupPermissions(GenericEvent $event)
    {
        $attributeGroup = $event->getSubject();
        if (!$attributeGroup instanceof AttributeGroupInterface || !$event->hasArgument('data')) {
            return;
        }

        $data = $event->getArgument('data');
        if (!isset($data['permissions'])) {
            return;
        }

        $this->attributeGroupAccessManager->setAccess(
            $attributeGroup,
            $this->getGroups($data['permissions']['execute']),
            $this->getGroups($data['permissions']['edit'])
        );
    }

    /**
     * @param GenericEvent $event
     */
    public function saveJobInstancePermissions(GenericEvent $event)
    {
        $jobInstance = $event->getSubject();
        if (!$jobInstance instanceof JobInstance || !$event->hasArgument('data')) {
            return;
        }

        $data = $event->getArgument('data');
        if (!isset($data['permissions'])) {
            return;
        }

        $this->jobInstanceAccessManager->setAccess(
            $jobInstance,
            $this->getGroups($data['permissions']['execute']),
            $this->getGroups($data['permissions']['edit'])
        );
    }

    /**
     * @param string[] $groupNames
     *
     * @return GroupInterface[]
     */
    private function getGroups($groupNames): iterable
    {
        return array_filter($this->userGroupRepository->findAll(), function ($group) use ($groupNames) {
            return in_array($group->getName(), $groupNames);
        });
    }
}
