<?php

namespace Specification\Akeneo\Platform\Bundle\UIBundle\ViewElement\Checker;

use Akeneo\Platform\Bundle\UIBundle\ViewElement\Checker\AclVisibilityChecker;
use Akeneo\Platform\Bundle\UIBundle\ViewElement\Checker\VisibilityCheckerInterface;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use PhpSpec\ObjectBehavior;

class AclVisibilityCheckerSpec extends ObjectBehavior
{
    function let(SecurityFacade $securityFacade)
    {
        $this->beConstructedWith($securityFacade);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AclVisibilityChecker::class);
    }

    function it_is_a_visibility_checker()
    {
        $this->shouldImplement(VisibilityCheckerInterface::class);
    }

    function it_requires_acl_in_the_configuration()
    {
        $this
            ->shouldThrow(new \InvalidArgumentException('The "acl" should be provided in the configuration.'))
            ->duringIsVisible();
    }

    function it_uses_the_acl_and_security_facade_to_determine_if_the_element_should_be_visible($securityFacade)
    {
        $securityFacade->isGranted('foo')->willReturn(true);
        $securityFacade->isGranted('bar')->willReturn(false);

        $this->isVisible(['acl' => 'foo'])->shouldReturn(true);
        $this->isVisible(['acl' => 'bar'])->shouldReturn(false);
    }
}
