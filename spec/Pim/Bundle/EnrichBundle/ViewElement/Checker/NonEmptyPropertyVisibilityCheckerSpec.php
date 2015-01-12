<?php

namespace spec\Pim\Bundle\EnrichBundle\ViewElement\Checker;

use PhpSpec\ObjectBehavior;

class NonEmptyPropertyVisibilityCheckerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\EnrichBundle\ViewElement\Checker\NonEmptyPropertyVisibilityChecker');
    }

    function it_is_a_visibility_checker()
    {
        $this->shouldImplement('Pim\Bundle\EnrichBundle\ViewElement\Checker\VisibilityCheckerInterface');
    }

    function it_requires_the_property_in_the_configuration()
    {
        $this
            ->shouldThrow(new \InvalidArgumentException('The "property" should be provided in the configuration.'))
            ->duringIsVisible();
    }

    function it_checks_if_the_given_property_exists_in_the_context()
    {
        $this->isVisible(['property' => '[foo]'], ['foo' => 1])->shouldReturn(true);

        $this->isVisible(['property' => '[foo]'], [])->shouldReturn(false);
    }

    function it_hides_the_element_if_property_exists_but_is_null()
    {
        $this->isVisible(['property' => '[bar]'], ['bar' => null])->shouldReturn(false);
    }
}
