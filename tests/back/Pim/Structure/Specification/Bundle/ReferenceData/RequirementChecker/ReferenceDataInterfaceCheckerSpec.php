<?php

namespace Specification\Akeneo\Pim\Structure\Bundle\ReferenceData\RequirementChecker;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\ReferenceDataConfigurationInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ReferenceDataInterface;

class ReferenceDataInterfaceCheckerSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(ReferenceDataInterface::class);
    }

    function it_checks_a_valid_reference_data(ReferenceDataConfigurationInterface $configuration)
    {
        $configuration->getClass()->willReturn(ReferenceDataColor::class);

        $this->check($configuration)->shouldReturn(true);
        $this->getFailure()->shouldReturn(null);
    }

    function it_checks_an_invalid_reference_data(ReferenceDataConfigurationInterface $configuration)
    {
        $configuration->getClass()->willReturn('\StdClass');

        $this->check($configuration)->shouldReturn(false);
        $this->getFailure()->shouldReturn(
            'Please implement "Akeneo\Pim\Enrichment\Component\Product\Model\ReferenceDataInterface" for your Reference Data model "\StdClass".'
        );
    }
}

class ReferenceDataColor implements ReferenceDataInterface
{
    public function getId()
    {
    }
    public function getCode()
    {
    }
    public function setCode($code)
    {
    }
    public function getSortOrder()
    {
    }
    public static function getLabelProperty()
    {
    }
    public function __toString()
    {
    }
}
