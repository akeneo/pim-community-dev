<?php

namespace Specification\Akeneo\Platform\Bundle\UIBundle\ViewElement\Checker;

use Akeneo\Platform\Bundle\UIBundle\ViewElement\Checker\VisibilityCheckerInterface;
use Akeneo\Platform\Bundle\UIBundle\ViewElement\Checker\VoterVisibilityChecker;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use PhpSpec\ObjectBehavior;

class VoterVisibilityCheckerSpec extends ObjectBehavior
{
    const OWN = 'OWN';
    const VIEW = 'VIEW';

    function let(SecurityFacade $securityFacade)
    {
        $this->beConstructedWith($securityFacade);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(VoterVisibilityChecker::class);
    }

    function it_is_a_visibility_checker()
    {
        $this->shouldImplement(VisibilityCheckerInterface::class);
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
        $securityFacade->isGranted(self::OWN, $object)->willReturn(true);
        $securityFacade->isGranted(self::VIEW, $object)->willReturn(false);

        $this->isVisible(['attribute' => 'Specification\Akeneo\Platform\Bundle\UIBundle\ViewElement\Checker\VoterVisibilityCheckerSpec::OWN', 'object' => $object])->shouldReturn(true);
        $this->isVisible(['attribute' => 'Specification\Akeneo\Platform\Bundle\UIBundle\ViewElement\Checker\VoterVisibilityCheckerSpec::VIEW', 'object' => $object])->shouldReturn(false);
    }

    function it_can_extract_the_object_from_the_context($securityFacade)
    {
        $object = new \stdClass();

        $securityFacade->isGranted(self::OWN, $object)->shouldBeCalled();

        $this->isVisible(['attribute' => 'Specification\Akeneo\Platform\Bundle\UIBundle\ViewElement\Checker\VoterVisibilityCheckerSpec::OWN', 'object' => '[foo][bar]'], ['foo' => ['bar' => $object]]);
    }
}
