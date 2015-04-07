<?php

namespace spec\Pim\Bundle\ReferenceDataBundle\RequirementChecker;

use PhpSpec\ObjectBehavior;
use Pim\Component\ReferenceData\Model\ConfigurationInterface;
use Pim\Component\ReferenceData\Model\ReferenceDataInterface;
use Prophecy\Argument;

class ReferenceDataInterfaceCheckerSpec extends ObjectBehavior
{
    function it_checks_a_valid_reference_data(ConfigurationInterface $configuration)
    {
        $configuration->getClass()->willReturn('spec\Pim\Bundle\ReferenceDataBundle\RequirementChecker\ReferenceDataColor');

        $this->check($configuration)->shouldReturn(true);
        $this->getFailure()->shouldReturn(null);
    }

    function it_checks_an_invalid_reference_data(ConfigurationInterface $configuration)
    {
        $configuration->getClass()->willReturn('\StdClass');

        $this->check($configuration)->shouldReturn(false);
        $this->getFailure()->shouldReturn(
            'Please implement "Pim\Component\ReferenceData\Model\ReferenceDataInterface" for your Reference Data model "\StdClass".'
        );
    }
}

class ReferenceDataColor implements ReferenceDataInterface
{
    public function getId() { }
    public function getCode() { }
    public function setCode($code) { }
    public function getType() { }
    public function getSortOrder() { }
    public function __toString() { }
}
