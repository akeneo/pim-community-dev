<?php

namespace Specification\Akeneo\Platform\Bundle\UIBundle;

use Akeneo\Platform\Bundle\UIBundle\PimEnterpriseUIBundle;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class PimEnterpriseUIBundleSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(PimEnterpriseUIBundle::class);
    }

    function it_is_a_bundle()
    {
        $this->shouldHaveType(Bundle::class);
    }

    function it_extends_the_community_ui_bundle()
    {
        $this->getParent()->shouldReturn('PimUIBundle');
    }
}
