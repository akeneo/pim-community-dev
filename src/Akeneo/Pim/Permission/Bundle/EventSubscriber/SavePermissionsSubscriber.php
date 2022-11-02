<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Permission\Bundle\EventSubscriber;

use Akeneo\Pim\Permission\Bundle\Manager\AttributeGroupAccessManager;
use Akeneo\Pim\Permission\Bundle\Manager\JobProfileAccessManager;
use Akeneo\Pim\Structure\Bundle\Event\AttributeGroupEvents;
use Akeneo\Pim\Structure\Component\Model\AttributeGroupInterface;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlags;
use Akeneo\Platform\Bundle\ImportExportBundle\Event\JobInstanceEvents;
use Akeneo\Platform\Bundle\ImportExportBundle\Exception\JobInstanceCannotBeUpdatedException;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\UserManagement\Component\Model\Group;
use Akeneo\UserManagement\Component\Model\GroupInterface;
use Doctrine\ORM\EntityRepository;
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
    public function __construct(
        private EntityRepository $userGroupRepository,
        private AttributeGroupAccessManager $attributeGroupAccessManager,
        private JobProfileAccessManager $jobInstanceAccessManager,
        private FeatureFlags $featureFlags
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            JobInstanceEvents::PRE_SAVE    => 'checkJobInstancePermissions',
            AttributeGroupEvents::POST_SAVE => 'saveAttributeGroupPermissions',
            JobInstanceEvents::POST_SAVE    => 'saveJobInstancePermissions',
        ];
    }

    /**
     * @param GenericEvent $event
     */
    public function saveAttributeGroupPermissions(GenericEvent $event)
    {
        if (!$this->featureFlags->isEnabled('permission')) {
            return;
        }

        $attributeGroup = $event->getSubject();
        if (!$attributeGroup instanceof AttributeGroupInterface || !$event->hasArgument('data')) {
            return;
        }

        $data = $event->getArgument('data');
        if (!isset($data['permissions'])) {
            return;
        }

        $currentViewUserGroups = $this->attributeGroupAccessManager->getViewUserGroups($attributeGroup);
        $currentEditUserGroups = $this->attributeGroupAccessManager->getEditUserGroups($attributeGroup);

        $this->attributeGroupAccessManager->setAccess(
            $attributeGroup,
            array_merge($this->getGroups($data['permissions']['view']), $this->filterHiddenGroups($currentViewUserGroups)),
            array_merge($this->getGroups($data['permissions']['edit']), $this->filterHiddenGroups($currentEditUserGroups)),
        );
    }

    /**
     * @param GenericEvent $event
     */
    public function checkJobInstancePermissions(GenericEvent $event)
    {
        $jobInstance = $event->getSubject();
        if (!$jobInstance instanceof JobInstance || !$event->hasArgument('data')) {
            return;
        }

        $data = $event->getArgument('data');
        if (!isset($data['permissions'])) {
            return;
        }

        if (empty($data['permissions']['edit'] ?? [])) {
            throw new JobInstanceCannotBeUpdatedException('pimee_import_export.entity.job_instance.flash.update.fail_empty_permission');
        }
    }

    /**
     * @param GenericEvent $event
     */
    public function saveJobInstancePermissions(GenericEvent $event)
    {
        if (!$this->featureFlags->isEnabled('permission')) {
            return;
        }

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

    /**
     * @param GroupInterface[] $groups
     *
     * @return GroupInterface[]
     */
    private function filterHiddenGroups(array $groups): array
    {
        return array_filter($groups, function ($group) {
            return $group->getType() !== Group::TYPE_DEFAULT;
        });
    }
}
