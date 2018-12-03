<?php

namespace Specification\Akeneo\Platform\Component\Authentication\Sso\Configuration;

use Akeneo\Platform\Component\Authentication\Sso\Configuration\Certificate;
use PhpSpec\ObjectBehavior;

class CertificateSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(Certificate::class);
    }

    function it_can_be_represented_as_string()
    {
        $this->beConstructedThrough('fromString', ['Ib3DQEBCwUAMEUxCzAJBgNVBAYTAkFVMRMw']);
        $this->toString()->shouldReturn('Ib3DQEBCwUAMEUxCzAJBgNVBAYTAkFVMRMw');
    }
}
