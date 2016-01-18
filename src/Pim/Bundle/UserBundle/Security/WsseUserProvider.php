<?php

namespace Pim\Bundle\UserBundle\Security;

use Escape\WSSEAuthenticationBundle\Security\Core\Authentication\Provider\Provider;
use Symfony\Component\Security\Core\User\UserInterface;

class WsseUserProvider extends Provider
{
    /**
     * @param UserInterface $user Instance of \Pim\Bundle\UserBundle\Entity\User
     *
     * @return string
     */
    protected function getSecret(UserInterface $user)
    {
        return $user->getApi()->getApiKey();
    }
}
