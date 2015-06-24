<?php

namespace spec\PimEnterprise\Bundle\ProductAssetBundle\JobLauncher;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class CommandLauncherSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('');
    }

    function it_can_be_initialized()
    {
        $this->shouldHaveType('PimEnterprise\Bundle\ProductAssetBundle\JobLauncher\CommandLauncher');
    }
}
