<?php

namespace Specification\Akeneo\Platform\Bundle\UIBundle\EventListener;

use Akeneo\Platform\Bundle\UIBundle\EventListener\ScriptNonceGenerator;
use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpKernel\KernelEvents;

class AddContentSecurityPolicyListenerSpec extends ObjectBehavior
{
    function let(ScriptNonceGenerator $scriptNonceGenerator)
    {
        $scriptNonceGenerator->getGeneratedNonce()->willReturn('generated_nonce');
        $this->beConstructedWith($scriptNonceGenerator);
    }

    function it_subscribes_to_kernel_response()
    {
        $this->getSubscribedEvents()->shouldReturn([KernelEvents::RESPONSE => 'addCspHeaders']);
    }
}
