<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\EventSubscriber;

use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Akeneo\UserManagement\Component\Model\Group;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupAllIsRemovedFromUsersUsedByAppsOnUpdateEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [StorageEvents::PRE_SAVE => 'removeGroupAllFromUsersUsedByApps'];
    }

    public function removeGroupAllFromUsersUsedByApps(GenericEvent $event): void
    {
        $subject = $event->getSubject();

        if (!$subject instanceof UserInterface) {
            return;
        }

        if (!$subject->isApiUser()) {
            return;
        }

        if (!$this->hasGroupOfTypeApp($subject)) {
            return;
        }

        $this->removeGroupAllFromUser($subject);
    }

    private function hasGroupOfTypeApp(UserInterface $user): bool
    {
        /** @var Group $group */
        foreach ($user->getGroups() as $group) {
            if ($group->getType() === 'app') {
                return true;
            }
        }

        return false;
    }

    private function removeGroupAllFromUser(UserInterface $user): void
    {
        /** @var Group $group */
        foreach ($user->getGroups() as $group) {
            if ($group->getName() === 'All') {
                $user->removeGroup($group);
                break;
            }
        }
    }
}
