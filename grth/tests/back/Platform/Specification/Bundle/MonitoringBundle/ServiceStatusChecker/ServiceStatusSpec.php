<?php

namespace Specification\Akeneo\Platform\Bundle\MonitoringBundle\ServiceStatusChecker;

use Akeneo\Platform\Bundle\MonitoringBundle\ServiceStatusChecker\ServiceStatus;
use PhpSpec\ObjectBehavior;

class ServiceStatusSpec extends ObjectBehavior
{
    function it_is_initializable_with_ko_information()
    {
        $this->beConstructedThrough('notOk', ['Something went wrong!']);
        $this->shouldHaveType(ServiceStatus::class);

        $this->isOk()->shouldReturn(false);
        $this->getMessage()->shouldReturn("Something went wrong!");
    }

    function it_is_initializable_with_ok_information()
    {
        $this->beConstructedThrough('ok', []);
        $this->shouldHaveType(ServiceStatus::class);
        $this->isOk()->shouldReturn(true);
        $this->getMessage()->shouldReturn('OK');
    }
}
