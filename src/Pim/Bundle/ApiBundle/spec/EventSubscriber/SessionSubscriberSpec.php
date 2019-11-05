<?php

namespace spec\Pim\Bundle\ApiBundle\EventSubscriber;

use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Http\FirewallMapInterface;

class SessionSubscriberSpec extends ObjectBehavior
{
    public function let(
        SessionInterface $session,
        RequestStack $requestStack,
        FirewallMapInterface $firewallMap
    ) {
        $this->beConstructedWith($session, $requestStack, $firewallMap);
    }
}
