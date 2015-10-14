<?php

namespace Pim\Bundle\UserBundle\EventSubscriber\Storage;

use Akeneo\Component\StorageUtils\StorageEvents;
use Pim\Bundle\UserBundle\Entity\Repository\GroupRepository;
use Pim\Bundle\UserBundle\Entity\User;
use Pim\Bundle\UserBundle\Entity\UserInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Aims to perform operations on user groups during creation, edition or deletion.
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddDefaultGroupToUserSubscriber implements EventSubscriberInterface
{
    /** @var GroupRepository $repository */
    protected $repository;

    /**
     * @param GroupRepository $repository
     */
    public function __construct(GroupRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            StorageEvents::PRE_SAVE => 'addDefaultGroup',
        ];
    }

    /**
     * Pre delete a user group
     *
     * @param GenericEvent $event
     */
    public function addDefaultGroup(GenericEvent $event)
    {
        $user = $event->getSubject();

        if (!$user instanceof UserInterface) {
            return;
        }

        if ($user->hasGroup(User::GROUP_DEFAULT)) {
            return;
        }

        $group = $this->repository->getDefaultUserGroup();

        if (!$group) {
            throw new \RuntimeException('Default user group not found');
        }

        $user->addGroup($group);
    }
}
