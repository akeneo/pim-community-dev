<?php

namespace spec\Pim\Bundle\ReferenceDataBundle\RequirementChecker;

use Doctrine\Common\Collections\Collection;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AbstractProductValue;
use Pim\Component\ReferenceData\Model\ConfigurationInterface;
use Pim\Component\ReferenceData\Model\ReferenceDataInterface;

class ProductValueAccessorsCheckerSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('spec\Pim\Bundle\ReferenceDataBundle\RequirementChecker\CustomValidProductValue');
    }

    function it_checks_a_valid_product_value_for_simple_reference_data(ConfigurationInterface $configuration)
    {
        $this->beConstructedWith('spec\Pim\Bundle\ReferenceDataBundle\RequirementChecker\CustomValidProductValue');

        $configuration->getType()->willReturn(ConfigurationInterface::TYPE_SIMPLE);
        $configuration->getName()->willReturn('color');

        $this->check($configuration)->shouldReturn(true);
        $this->getFailure()->shouldReturn(null);
    }

    function it_checks_a_valid_product_value_for_multiple_reference_data(ConfigurationInterface $configuration)
    {
        $this->beConstructedWith('spec\Pim\Bundle\ReferenceDataBundle\RequirementChecker\CustomValidProductValue');

        $configuration->getType()->willReturn(ConfigurationInterface::TYPE_MULTI);
        $configuration->getName()->willReturn('fabrics');

        $this->check($configuration)->shouldReturn(true);
        $this->getFailure()->shouldReturn(null);
    }

    function it_checks_an_invalid_product_value_for_simple_reference_data(ConfigurationInterface $configuration)
    {
        $this->beConstructedWith('spec\Pim\Bundle\ReferenceDataBundle\RequirementChecker\CustomInvalidProductValue');

        $configuration->getType()->willReturn(ConfigurationInterface::TYPE_SIMPLE);
        $configuration->getName()->willReturn('color');

        $this->check($configuration)->shouldReturn(false);
        $this->getFailure()->shouldReturn(
            'Please implement the accessors "getColor, setColor" for ' .
            '"spec\Pim\Bundle\ReferenceDataBundle\RequirementChecker\CustomInvalidProductValue".'
        );
    }

    function it_checks_an_invalid_product_value_for_multiple_reference_data(ConfigurationInterface $configuration)
    {
        $this->beConstructedWith('spec\Pim\Bundle\ReferenceDataBundle\RequirementChecker\CustomInvalidProductValue');

        $configuration->getType()->willReturn(ConfigurationInterface::TYPE_MULTI);
        $configuration->getName()->willReturn('fabrics');

        $this->check($configuration)->shouldReturn(false);
        $this->getFailure()->shouldReturn(
            'Please implement the accessors "getFabrics, setFabrics, addFabric, removeFabric" for ' .
            '"spec\Pim\Bundle\ReferenceDataBundle\RequirementChecker\CustomInvalidProductValue".'
        );
    }
}

class CustomValidProductValue extends AbstractProductValue
{
    public function setColor(ReferenceDataInterface $referenceData)
    {
    }
    public function getColor()
    {
    }
    public function setFabrics(Collection $fabrics)
    {
    }
    public function getFabrics()
    {
    }
    public function addFabric(ReferenceDataInterface $referenceData)
    {
    }
    public function removeFabric(ReferenceDataInterface $referenceData)
    {
    }
}

class CustomInvalidProductValue extends AbstractProductValue
{
}
