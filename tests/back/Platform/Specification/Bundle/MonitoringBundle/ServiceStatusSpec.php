<?php

namespace Specification\Akeneo\Platform\Bundle\MonitoringBundle;

use Akeneo\Platform\Bundle\MonitoringBundle\ServiceStatus;
use PhpSpec\ObjectBehavior;

class ServiceStatusSpec extends ObjectBehavior
{
    function it_is_initializable_with_ko_information()
    {
        $this->beConstructedWith(false, "Something went wrong!");
        $this->shouldHaveType(ServiceStatus::class);

        $this->isOk()->shouldReturn(false);
        $this->getMessage()->shouldReturn("Something went wrong!");
    }

    function it_is_initializable_with_ok_information()
    {
        $this->beConstructedWith(true, "Everything is alright.");
        $this->shouldHaveType(ServiceStatus::class);
        $this->isOk()->shouldReturn(true);
        $this->getMessage()->shouldReturn("Everything is alright.");
    }
}
