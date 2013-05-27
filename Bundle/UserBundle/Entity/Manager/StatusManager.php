<?php

namespace Oro\Bundle\UserBundle\Entity\Manager;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\UserBundle\Entity\Status;
use Oro\Bundle\UserBundle\Entity\UserManager;

class StatusManager
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var UserManager
     */
    private $um;

    public function __construct(EntityManager $em, UserManager $um)
    {
        $this->em = $em;
        $this->um = $um;
    }

    /**
     * @param  User $user
     * @return Status[]
     */
    public function getUserStatuses(User $user)
    {
        return $this->getStatusRepo()->findBy(array('user' => $user));
    }

    /**
     * Delete user status
     *
     * @param  User $user
     * @param  Status $status
     * @param  bool $reloadUser
     * @return bool
     */
    public function deleteStatus(User $user, Status $status, $reloadUser = true)
    {
        if ($status->getUser() == $user) {
            if ($user->getCurrentStatus() == $status) {
                $user->setCurrentStatus();
                $this->um->updateUser($user);
                if ($reloadUser) {
                    $this->um->reloadUser($user);
                }
            }
            $this->em->remove($status);
            $this->em->flush();

            return true;
        }

        return false;
    }

    /**
     * Set status as current
     *
     * @param User $user
     * @param Status $status
     * @param bool $reloadUser
     */
    public function setCurrentStatus(User $user, Status $status = null, $reloadUser = true)
    {
        $user->setCurrentStatus($status);
        $this->um->updateUser($user);
        if ($reloadUser) {
            $this->um->reloadUser($user);
        }
    }

    /**
     * @return EntityRepository
     */
    protected function getStatusRepo()
    {
        return $this->em->getRepository('OroUserBundle:Status');
    }
}
