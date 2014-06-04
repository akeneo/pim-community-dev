<?php

namespace spec\PimEnterprise\Bundle\EnrichBundle\MassEditAction\Operation;

use PhpSpec\ObjectBehavior;
use PimEnterprise\Bundle\CatalogBundle\Manager\CategoryManager;
use Prophecy\Argument;

class ClassifySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Bundle\EnrichBundle\MassEditAction\Operation\Classify');
    }

    function it_should_be_a_Classify_class()
    {
        $this->shouldHaveType('Pim\Bundle\EnrichBundle\MassEditAction\Operation\Classify');
    }

    function let(CategoryManager $categoryManager)
    {
        $this->beConstructedWith($categoryManager);
    }
}
