<?php

namespace Specification\Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\NomenclatureDefinition;
use PhpSpec\ObjectBehavior;

class NomenclatureDefinitionSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('<=', 3);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(NomenclatureDefinition::class);
    }

    function it_has_an_operator()
    {
        $this->operator()->shouldReturn('<=');
    }

    function it_clones_with_operator()
    {
        $this->withOperator('=')->shouldBeLike(new NomenclatureDefinition('=', 3));
    }

    function it_has_a_value()
    {
        $this->value()->shouldReturn(3);
    }

    function it_clones_with_value()
    {
        $this->withValue(5)->shouldBeLike(new NomenclatureDefinition('<=', 5));
    }
}
