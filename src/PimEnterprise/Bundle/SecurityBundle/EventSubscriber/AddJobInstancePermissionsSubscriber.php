<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\SecurityBundle\EventSubscriber;

use Akeneo\Component\Batch\Model\JobInstance;
use Akeneo\Component\StorageUtils\StorageEvents;
use Pim\Bundle\UserBundle\Doctrine\ORM\Repository\GroupRepository;
use PimEnterprise\Bundle\SecurityBundle\Manager\JobProfileAccessManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Adds the default permissions when a job instance is created.
 *
 * @author Yohan Blain <yohan.blain@akeneo.com>
 */
class AddJobInstancePermissionsSubscriber implements EventSubscriberInterface
{
    /** @var JobProfileAccessManager */
    protected $accessManager;

    /** @var GroupRepository */
    protected $groupRepository;

    /**
     * @param JobProfileAccessManager $accessManager
     * @param GroupRepository         $groupRepository
     */
    public function __construct(
        JobProfileAccessManager $accessManager,
        GroupRepository $groupRepository
    ) {
        $this->accessManager = $accessManager;
        $this->groupRepository = $groupRepository;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            StorageEvents::POST_SAVE => 'setDefaultPermissions'
        ];
    }

    /**
     * Set the default permissions to the new job instance.
     *
     * @param GenericEvent $event
     */
    public function setDefaultPermissions(GenericEvent $event)
    {
        $jobInstance = $event->getSubject();

        if ($jobInstance instanceof JobInstance && $event->hasArgument('is_new') && $event->getArgument('is_new')) {
            $defaultGroup = $this->groupRepository->getDefaultUserGroup();

            $this->accessManager->setAccess($jobInstance, [$defaultGroup], [$defaultGroup]);
        }
    }
}
