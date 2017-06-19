<?php

namespace spec\Pim\Bundle\EnrichBundle\MassEditAction\Operation;

use Oro\Bundle\SecurityBundle\SecurityFacade;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\EnrichBundle\MassEditAction\Operation\MassEditOperationInterface;
use Pim\Bundle\EnrichBundle\MassEditAction\Operation\OperationRegistryInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class OperationRegistrySpec extends ObjectBehavior
{
    function let(TokenStorageInterface $tokenStorage, SecurityFacade $securityFacade, TokenInterface $token)
    {
        $tokenStorage->getToken()->willReturn($token);
        $this->beConstructedWith($tokenStorage, $securityFacade);
    }

    function it_implements_operation_registry_interface()
    {
        $this->shouldHaveType(OperationRegistryInterface::class);
    }

    function it_registers_a_mass_edit_operation_and_retrieves_it_by_its_alias(
        $securityFacade,
        MassEditOperationInterface $gridOperation,
        MassEditOperationInterface $aclOperation
    ) {
        $this->register($gridOperation, 'grid', 'product-grid', 'mass-edit');
        $this->get('grid')->shouldReturn($gridOperation);

        $securityFacade->isGranted('acl1')->willReturn(true);
        $this->register($aclOperation, 'acl1', 'mass_edit_grid', 'mass-edit', 'acl1');
        $this->get('acl1')->shouldReturn($aclOperation);

        $securityFacade->isGranted('acl2')->willReturn(false);
        $this->register($aclOperation, 'acl2', 'mass_edit_grid', 'mass-edit', 'acl2');
        $this->shouldThrow('\InvalidArgumentException')->during('get', ['acl2']);
    }

    function it_retrieves_all_operation_registered_with_a_gridname_and_group(
        MassEditOperationInterface $dummyOperation,
        MassEditOperationInterface $gridOperation,
        MassEditOperationInterface $awesomeOperation,
        MassEditOperationInterface $amazingOperation
    ) {
        $this->register($dummyOperation, 'dummy', 'product-grid', 'mass-edit');
        $this->register($gridOperation, 'grid', 'product-grid', 'mass-edit');
        $this->register($awesomeOperation, 'awesome', 'product-grid', 'category-edit');
        $this->register($amazingOperation, 'amazing', 'family-grid', 'mass-edit');

        $this->getAllByGridNameAndGroup('product-grid', 'mass-edit')->shouldHaveCount(2);
        $this->getAllByGridNameAndGroup('product-grid', 'category-edit')->shouldHaveCount(1);
        $this->getAllByGridNameAndGroup('family-grid', 'mass-edit')->shouldHaveCount(1);
    }

    function it_throws_an_exception_if_an_operation_is_already_registered(
        MassEditOperationInterface $dummyOperation,
        MassEditOperationInterface $amazingOperation
    ) {
        $this->register($dummyOperation, 'dummy', 'product-grid', 'mass-edit');
        $this->shouldThrow('\InvalidArgumentException')
            ->during('register', [$amazingOperation, 'dummy', 'product-grid', 'mass-edit']);
    }

    function it_throws_an_exception_if_no_operation_is_found_with_alias()
    {
        $this->shouldThrow('\InvalidArgumentException')
            ->during('get', ['operation404']);
    }

    function it_throws_an_exception_if_no_operation_is_found_with_gridname()
    {
        $this->shouldThrow('\InvalidArgumentException')
            ->during('getAllByGridNameAndGroup', ['grid404', 'group404']);
    }
}
