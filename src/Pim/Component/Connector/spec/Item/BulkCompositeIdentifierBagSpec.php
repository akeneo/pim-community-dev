<?php

namespace spec\Pim\Component\Connector\Item;

use PhpSpec\ObjectBehavior;

class BulkCompositeIdentifierBagSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Connector\Item\BulkCompositeIdentifierBag');

    }

    function it_is_a_bulk_identifier_bag()
    {
        $this->shouldImplement('Pim\Component\Connector\Item\BulkIdentifierBagInterface');
    }

    function is_adds_a_new_composite_identifier()
    {
        $this->add(['sku-1', 'other-id-1']);
        $this->has(['sku-1', 'other-id-1'])->shouldReturn(true);
    }

    function it_throws_a_logic_exception_when_it_tries_to_add_an_identifier_that_is_not_an_array()
    {
        $this->shouldThrow(
            new \InvalidArgumentException('The identifier "sku-1" is not a composite key (an array).')
        )->during('add', ['sku-1']);
    }

    function it_throws_a_logic_exception_when_it_adds_a_composite_identifier_it_already_contains()
    {
        $this->add(['sku-1', 'other-id-1']);
        $this->shouldThrow(
            new \LogicException('The composite identifier "sku-1, other-id-1" is already contained in the bag.')
        )->during('add', [['sku-1', 'other-id-1']]);
    }

    function it_resets_the_composite_idenfitiers_bag()
    {
        $this->add(['sku-1', 'id-1']);
        $this->add(['sku-2', 'id-2']);

        $this->has(['sku-1', 'id-1'])->shouldReturn(true);
        $this->has(['sku-2', 'id-2'])->shouldReturn(true);

        $this->reset();

        $this->has(['sku-1', 'id-1'])->shouldReturn(false);
        $this->has(['sku-2', 'id-2'])->shouldReturn(false);
    }
}
