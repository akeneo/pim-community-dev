<?php

namespace spec\Akeneo\Tool\Bundle\ElasticsearchBundle;

use Akeneo\Tool\Bundle\ElasticsearchBundle\Refresh;
use PhpSpec\ObjectBehavior;

class RefreshSpec extends ObjectBehavior
{
    function it_creates_a_enable_refresh_param()
    {
        $this->beConstructedThrough('enable');
        $this->getType()->shouldReturn(Refresh::ENABLE);
    }

    function it_creates_a_disable_refresh_param()
    {
        $this->beConstructedThrough('disable');
        $this->getType()->shouldReturn(Refresh::DISABLE);
    }

    function it_creates_a_wait_for_refresh_param()
    {
        $this->beConstructedThrough('waitFor');
        $this->getType()->shouldReturn(Refresh::WAIT_FOR);
    }
}
