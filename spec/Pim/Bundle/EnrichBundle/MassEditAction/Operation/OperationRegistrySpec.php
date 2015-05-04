<?php

namespace spec\Pim\Bundle\EnrichBundle\MassEditAction\Operation;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\EnrichBundle\MassEditAction\Operation\MassEditOperationInterface;

class OperationRegistrySpec extends ObjectBehavior
{
    function it_implements_operation_registry_interface()
    {
        $this->shouldHaveType('Pim\Bundle\EnrichBundle\MassEditAction\Operation\OperationRegistryInterface');
    }

    function it_registers_a_mass_edit_operation_and_retrieves_it_by_its_alias(
        MassEditOperationInterface $dummyOperation,
        MassEditOperationInterface $gridOperation,
        MassEditOperationInterface $aclOperation
    ) {
        $this->register($dummyOperation, 'dummy');
        $this->get('dummy')->shouldReturn($dummyOperation);

        $this->register($gridOperation, 'grid', null, 'product-grid');
        $this->get('grid')->shouldReturn($gridOperation);

        $this->register($aclOperation, 'acl', 'mass_edit_grid');
        $this->get('acl')->shouldReturn($aclOperation);
    }

    function it_retrieves_all_operation_registered_with_a_gridname(
        MassEditOperationInterface $dummyOperation,
        MassEditOperationInterface $gridOperation,
        MassEditOperationInterface $amazingOperation
    ) {
        $this->register($dummyOperation, 'dummy', null, 'product-grid');
        $this->register($gridOperation, 'grid', null, 'product-grid');
        $this->register($amazingOperation, 'amazing', null, 'family-grid');

        $this->getAllByGridName('product-grid')->shouldHaveCount(2);
        $this->getAllByGridName('family-grid')->shouldHaveCount(1);
    }

    function it_throws_an_exception_if_an_operation_is_already_registered(
        MassEditOperationInterface $dummyOperation,
        MassEditOperationInterface $amazingOperation
    ) {
        $this->register($dummyOperation, 'dummy');
        $this->shouldThrow('\InvalidArgumentException')
            ->during('register', [$amazingOperation, 'dummy']);
    }

    function it_throws_an_exception_if_no_operation_is_found_with_alias()
    {
        $this->shouldThrow('\InvalidArgumentException')
            ->during('get', ['operation404']);
    }

    function it_throws_an_exception_if_no_operation_is_found_with_gridname()
    {
        $this->shouldThrow('\InvalidArgumentException')
            ->during('getAllByGridName', ['grid404']);
    }
}
