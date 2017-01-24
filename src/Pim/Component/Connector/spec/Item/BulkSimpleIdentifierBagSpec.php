<?php

namespace spec\Pim\Component\Connector\Item;

use PhpSpec\ObjectBehavior;

class BulkSimpleIdentifierBagSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Connector\Item\BulkSimpleIdentifierBag');
    }

    function it_is_a_bulk_identifier_bag()
    {

        $this->shouldImplement('Pim\Component\Connector\Item\BulkIdentifierBagInterface');
    }

    function it_adds_a_new_identifier()
    {
        $this->add('sku-1');
        $this->has('sku-1')->shouldReturn(true);
    }

    function it_throws_a_logic_exception_when_it_adds_an_identifier_it_already_contains()
    {
        $this->add('sku-1');
        $this->shouldThrow(
            new \LogicException('The identifier "sku-1" is already contained in the bag.')
        )->during('add', ['sku-1']);
    }

    function it_resets_the_idenfitiers()
    {
        $this->add('sku-1');
        $this->add('sku-2');

        $this->has('sku-1')->shouldReturn(true);
        $this->has('sku-2')->shouldReturn(true);

        $this->reset();

        $this->has('sku-1')->shouldReturn(false);
        $this->has('sku-2')->shouldReturn(false);
    }
}
