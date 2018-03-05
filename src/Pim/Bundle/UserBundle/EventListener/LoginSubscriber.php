<?php

namespace Pim\Bundle\UserBundle\EventListener;

use Pim\Bundle\UserBundle\Manager\UserManager;
use Pim\Component\User\User\UserInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class LoginSubscriber
{
    protected $userManager;

    public function __construct(UserManager $userManager)
    {
        $this->userManager = $userManager;
    }

    public function onLogin(InteractiveLoginEvent $event)
    {
        $user = $event->getAuthenticationToken()->getUser();

        if ($user instanceof UserInterface) {
            $user->setLastLogin(new \DateTime('now', new \DateTimeZone('UTC')))
                 ->setLoginCount($user->getLoginCount() + 1);

            $this->userManager->updateUser($user);
        }
    }
}
