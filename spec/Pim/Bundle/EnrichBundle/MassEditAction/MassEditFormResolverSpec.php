<?php

namespace spec\Pim\Bundle\EnrichBundle\MassEditAction;

use Oro\Bundle\RequireJSBundle\Provider\Config;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\EnrichBundle\Form\Type\MassEditChooseActionType;
use Pim\Bundle\EnrichBundle\MassEditAction\Operation\ConfigurableOperationInterface;
use Pim\Bundle\EnrichBundle\MassEditAction\Operation\MassEditOperationInterface;
use Pim\Bundle\EnrichBundle\MassEditAction\Operation\OperationRegistryInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

class MassEditFormResolverSpec extends ObjectBehavior
{
    function let(
        OperationRegistryInterface $operationRegistry,
        FormFactoryInterface $formFactory,
        MassEditChooseActionType $chooseActionFormType
    ) {
        $this->beConstructedWith(
            $operationRegistry,
            $formFactory,
            $chooseActionFormType
        );
    }

    function it_returns_the_form_for_all_available_operations(
        $operationRegistry,
        $formFactory,
        $chooseActionFormType,
        ConfigurableOperationInterface $duplicateOperation,
        ConfigurableOperationInterface $eraseOperation
    ) {
        $gridname = 'awesome-grid';

        $operationRegistry->getAllByGridName($gridname)->willReturn([
            'duplicate' => $duplicateOperation,
            'erase'     => $eraseOperation
        ]);

        $formFactory->create($chooseActionFormType, null, [
            'operations' =>
            [
                'duplicate' => 'pim_enrich.mass_edit_action.duplicate.label',
                'erase'     => 'pim_enrich.mass_edit_action.erase.label'
            ]
        ])->shouldBeCalled();

        $this->getAvailableOperationsForm($gridname);
    }

    function it_returns_the_operation_configuration_form(
        $operationRegistry,
        $formFactory,
        $chooseActionFormType,
        ConfigurableOperationInterface $operation,
        FormInterface $form
    ) {
        $operationAlias = 'add-to-group';

        $operation->getFormOptions()->willReturn([]);
        $operation->getFormType()->willReturn('add_to_group_type');

        $operationRegistry->get($operationAlias)->willReturn($operation);

        $formFactory->create($chooseActionFormType, null, [])->willReturn($form);
        $form->add('operation', 'add_to_group_type', [])->shouldBeCalled();

        $this->getConfigurationForm($operationAlias)->shouldReturn($form);
    }

    function it_throws_an_exception_the_operation_is_not_configurable(
        $operationRegistry,
        MassEditOperationInterface $operation
    ) {
        $operationAlias = 'update-title';
        $operationRegistry->get($operationAlias)->willReturn($operation);

        $this->shouldThrow('LogicException')->during('getConfigurationForm', [$operationAlias]);
    }
}
