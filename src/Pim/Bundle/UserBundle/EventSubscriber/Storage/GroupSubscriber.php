<?php

namespace Pim\Bundle\UserBundle\EventSubscriber\Storage;

use Akeneo\Component\StorageUtils\StorageEvents;
use Pim\Bundle\UserBundle\Entity\Group;
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
            StorageEvents::PRE_REMOVE => 'preDeleteGroup',
            StorageEvents::PRE_SAVE   => 'preUpdateGroup',
        ];
    }

    /**
     * Pre delete a user group
     *
     * @param GenericEvent $event
     */
    public function preDeleteGroup(GenericEvent $event)
    {
        $group = $event->getSubject();

        if (!$group instanceof Group) {
            return;
        }

        if (strtolower(User::GROUP_DEFAULT) === strtolower($group->getName())) {
            throw new \Exception(sprintf('The default group "%s" can not be updated.', $group->getName()));
        }
    }

    /**
     * Pre update a user group
     *
     * @param GenericEvent $event
     */
    public function preUpdateGroup(GenericEvent $event)
    {
        $group = $event->getSubject();

        if (!$group instanceof Group) {
            return;
        }

        if (strtolower(User::GROUP_DEFAULT) === strtolower($group->getName())) {
            throw new \Exception(sprintf('The default group "%s" can not be updated.', $group->getName()));
        }
    }
}
