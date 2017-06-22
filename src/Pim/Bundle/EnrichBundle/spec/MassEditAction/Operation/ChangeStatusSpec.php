<?php

namespace spec\Pim\Bundle\EnrichBundle\MassEditAction\Operation;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\EnrichBundle\Form\Type\MassEditAction\ChangeStatusType;
use Pim\Bundle\EnrichBundle\MassEditAction\Operation\BatchableOperationInterface;
use Pim\Bundle\EnrichBundle\MassEditAction\Operation\ConfigurableOperationInterface;
use Pim\Bundle\EnrichBundle\MassEditAction\Operation\MassEditOperationInterface;

class ChangeStatusSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('update_product_value');
    }

    function it_is_a_mass_edit_operation()
    {
        $this->shouldImplement(MassEditOperationInterface::class);
        $this->shouldImplement(ConfigurableOperationInterface::class);
        $this->shouldImplement(BatchableOperationInterface::class);
    }

    function it_stores_the_state_to_apply_to_the_products()
    {
        $this->isToEnable()->shouldReturn(false);
        $this->setToEnable(true);
        $this->isToEnable()->shouldReturn(true);

        $this->setToEnable(false);
        $this->isToEnable()->shouldReturn(false);
    }

    function it_provides_a_form_type()
    {
        $this->getFormType()->shouldReturn(ChangeStatusType::class);
    }

    function it_provides_form_options()
    {
        $this->getFormOptions()->shouldReturn([]);
    }

    function it_provides_an_alias()
    {
        $this->getOperationAlias()->shouldReturn('change-status');
    }

    function it_provides_correct_actions_to_apply_on_products()
    {
        $this->setToEnable(true);

        $this->getActions()->shouldReturn(
            [
                [
                    'field' => 'enabled',
                    'value' => true
                ]
            ]
        );

        $this->setToEnable(false);

        $this->getActions()->shouldReturn(
            [
                [
                    'field' => 'enabled',
                    'value' => false
                ]
            ]
        );
    }

    function it_provides_a_batch_job_code()
    {
        $this->getJobInstanceCode()->shouldReturn('update_product_value');
    }

    function it_provides_formatted_batch_config_for_the_job()
    {
        $this->setToEnable(true);
        $this->setFilters([
            ['id', 'IN', ['98', '99', '100']]
        ]);

        $this->getBatchConfig()->shouldReturn(
            [
                'filters' => [['id', 'IN', ['98', '99', '100']]],
                'actions' => [['field' => 'enabled', 'value' => true]]
            ]
        );

        $this->setToEnable(false);
        $this->setFilters([
            ['id', 'IN', ['98', '99', '100']]
        ]);

        $this->getBatchConfig()->shouldReturn(
            [
                'filters' => [['id', 'IN', ['98', '99', '100']]],
                'actions' => [['field' => 'enabled', 'value' => false]]
            ]
        );
    }
}
