<?php

namespace spec\Pim\Bundle\EnrichBundle\ViewElement\Checker;

use Oro\Bundle\SecurityBundle\SecurityFacade;
use PhpSpec\ObjectBehavior;

class VoterVisibilityCheckerSpec extends ObjectBehavior
{
    function let(SecurityFacade $securityFacade)
    {
        $this->beConstructedWith($securityFacade);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\EnrichBundle\ViewElement\Checker\VoterVisibilityChecker');
    }

    function it_is_a_visibility_checker()
    {
        $this->shouldImplement('Pim\Bundle\EnrichBundle\ViewElement\Checker\VisibilityCheckerInterface');
    }

    function it_requires_the_attribute_and_object_in_the_configuration()
    {
        $this
            ->shouldThrow(new \InvalidArgumentException('The "attribute" should be provided in the configuration.'))
            ->duringIsVisible();

        $this
            ->shouldThrow(new \InvalidArgumentException('The "object" should be provided in the configuration.'))
            ->duringIsVisible(['attribute' => 'foo']);
    }

    function it_uses_the_attribute_and_object_and_security_facade_to_determine_if_the_element_should_be_visible(
        $securityFacade
    ) {
        $object = new \stdClass();
        $securityFacade->isGranted('foo', $object)->willReturn(true);
        $securityFacade->isGranted('bar', $object)->willReturn(false);

        $this->isVisible(['attribute' => 'foo', 'object' => $object])->shouldReturn(true);
        $this->isVisible(['attribute' => 'bar', 'object' => $object])->shouldReturn(false);
    }

    function it_can_extract_the_object_from_the_context($securityFacade)
    {
        $object = new \stdClass();

        $securityFacade->isGranted('foo', $object)->shouldBeCalled();

        $this->isVisible(['attribute' => 'foo', 'object' => '__context[foo][bar]'], ['foo' => ['bar' => $object]]);
    }
}
