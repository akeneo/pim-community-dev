<?php

namespace Oro\Bundle\UserBundle\Datagrid;

use Doctrine\ORM\QueryBuilder;

use Oro\Bundle\EmailBundle\Datagrid\EmailDatagridManager;
use Oro\Bundle\UserBundle\Entity\User;

class UserEmailDatagridManager extends EmailDatagridManager
{
    /**
     * @var User
     */
    protected $user;

    /**
     * @param User $user
     */
    public function setUser(User $user)
    {
        $this->user = $user;
        $this->routeGenerator->setRouteParameters(array('id' => $user->getId()));
    }

    /**
     * @return User
     * @throws \LogicException
     */
    public function getUser()
    {
        if (!$this->user) {
            throw new \LogicException('Datagrid manager has no configured User entity');
        }

        return $this->user;
    }

    /**
     * {@inheritDoc}
     */
    protected function prepareQuery(QueryBuilder $query)
    {
        $origin = $this->user->getImapConfiguration();
        $query->setParameter('origin_id', $origin !== null ? $origin->getId() : null);
    }
}
