<?php

namespace spec\Pim\Bundle\CatalogBundle\Entity;

use Pim\Bundle\CatalogBundle\Entity\AttributeOption;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeOptionValueInterface;
use Prophecy\Argument;

class AttributeOptionSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(AttributeOption::class);
    }

    function it_returns_null_when_there_is_no_translation()
    {
        $this->getOptionValue()->shouldReturn(null);
    }

    function it_returns_the_expected_translation(AttributeOptionValueInterface $en, AttributeOptionValueInterface $fr)
    {
        $en->getLocale()->willReturn('en');
        $fr->getLocale()->willReturn('fr');

        $en->setOption(Argument::any())->shouldBeCalled();
        $fr->setOption(Argument::any())->shouldBeCalled();

        $this->addOptionValue($en);
        $this->addOptionValue($fr);
        $this->setLocale('fr');

        $this->getOptionValue()->shouldReturn($fr);
    }
}
