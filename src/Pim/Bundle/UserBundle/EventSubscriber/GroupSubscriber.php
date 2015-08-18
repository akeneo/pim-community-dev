<?php

namespace Pim\Bundle\UserBundle\EventSubscriber;

use Oro\Bundle\UserBundle\Entity\Group;
use Oro\Bundle\UserBundle\OroUserEvents;
use Pim\Bundle\UserBundle\Entity\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Aims to perform operations on user groups during creation, edition or deletion.
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupSubscriber implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            OroUserEvents::PRE_DELETE_GROUP => 'preDeleteGroup',
            OroUserEvents::PRE_UPDATE_GROUP => 'preUpdateGroup',
        ];
    }

    /**
     * Pre delete a user group
     *
     * @param GenericEvent $event
     */
    public function preDeleteGroup(GenericEvent $event)
    {
        $this->checkDefaultGroup($event);
    }

    /**
     * Pre update a user group
     *
     * @param GenericEvent $event
     */
    public function preUpdateGroup(GenericEvent $event)
    {
        $this->checkDefaultGroup($event);
    }

    /**
     * Check if the current user group is the default group.
     *
     * @param GenericEvent $event
     *
     * @throws \Exception
     */
    protected function checkDefaultGroup(GenericEvent $event)
    {
        /** @var Group $group */
        $group = $event->getSubject();

        if (strtolower(User::GROUP_DEFAULT) === strtolower($group->getName())) {
            $event->stopPropagation();
            throw new \Exception(sprintf('The default group "%s" can not be updated.', $group->getName()));
        }
    }
}
