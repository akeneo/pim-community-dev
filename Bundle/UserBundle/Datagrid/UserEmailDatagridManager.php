<?php

namespace Oro\Bundle\UserBundle\Datagrid;

use Oro\Bundle\EmailBundle\Datagrid\EmailDatagridManager;
use Oro\Bundle\EmailBundle\Entity\Util\EmailUtil;
use Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface;
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
    protected function prepareQuery(ProxyQueryInterface $query)
    {
        $origin = $this->user->getImapConfiguration();
        $query->setParameter('origin_id', $origin !== null ? $origin->getId() : null);
        $userEmails = EmailUtil::extractEmailAddresses($this->user->getEmails());
        $primaryEmail = $this->user->getEmail();
        if (!empty($primaryEmail)) {
            $userEmails[] = $this->user->getEmail();
        }
        $query->setParameter('user_emails', empty($userEmails) ? null : $userEmails);
    }
}
