<?php

namespace spec\Pim\Bundle\ReferenceDataBundle\RequirementChecker;

use PhpSpec\ObjectBehavior;
use Pim\Component\ReferenceData\Model\ConfigurationInterface;

class ReferenceDataNameCheckerSpec extends ObjectBehavior
{
    function it_checks_a_valid_reference_data(ConfigurationInterface $configuration)
    {
        $configuration->getName()->willReturn('fabrics');

        $this->check($configuration)->shouldReturn(true);
        $this->getFailure()->shouldReturn(null);
    }

    function it_checks_an_invalid_reference_data(ConfigurationInterface $configuration)
    {
        $configuration->getName()->willReturn('main-color');

        $this->check($configuration)->shouldReturn(false);
        $this->getFailure()->shouldReturn(
            'Please use a proper name instead of "main-color" for your Reference Data.'
        );
    }
}
