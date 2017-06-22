<?php

namespace spec\Pim\Bundle\EnrichBundle\MassEditAction\Operation;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\EnrichBundle\Form\Type\MassEditAction\ChangeFamilyType;
use Pim\Bundle\EnrichBundle\MassEditAction\Operation\BatchableOperationInterface;
use Pim\Bundle\EnrichBundle\MassEditAction\Operation\ConfigurableOperationInterface;
use Pim\Bundle\EnrichBundle\MassEditAction\Operation\MassEditOperationInterface;
use Pim\Component\Catalog\Model\FamilyInterface;

class ChangeFamilySpec extends ObjectBehavior
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

    function it_stores_the_family_to_add_the_products_to(FamilyInterface $mugs)
    {
        $this->getFamily()->shouldReturn(null);

        $this->setFamily($mugs);

        $this->getFamily()->shouldReturn($mugs);
        $this->getFamily()->shouldBeAnInstanceOf(FamilyInterface::class);
    }

    function it_provides_a_form_type()
    {
        $this->getFormType()->shouldReturn(ChangeFamilyType::class);
    }

    function it_provides_form_options()
    {
        $this->getFormOptions()->shouldReturn([]);
    }

    function it_provides_an_alias()
    {
        $this->getOperationAlias()->shouldReturn('change-family');
    }

    function it_provides_correct_actions_to_apply_on_products(FamilyInterface $mugs)
    {
        $mugs->getCode()->willReturn('amazing_mugs');
        $this->setFamily($mugs);

        $this->getActions()->shouldReturn(
            [
                [
                    'field' => 'family',
                    'value' => 'amazing_mugs',
                ]
            ]
        );
    }

    function it_sets_value_to_null_if_no_family_set()
    {
        $this->getFamily()->shouldReturn(null);

        $this->getActions()->shouldReturn(
            [
                [
                    'field' => 'family',
                    'value' => null,
                ]
            ]
        );
    }

    function it_provides_a_batch_job_code()
    {
        $this->getJobInstanceCode()->shouldReturn('update_product_value');
    }

    function it_provides_formatted_batch_config_for_the_job(FamilyInterface $mugs)
    {
        $mugs->getCode()->willReturn('amazing_mugs');
        $this->setFamily($mugs);
        $this->setFilters(
            [
                ['id', 'IN', ['1003', '1002']]
            ]
        );

        $this->getBatchConfig()->shouldReturn(
            [
                'filters' => [['id', 'IN', ['1003', '1002']]],
                'actions' => [['field' => 'family', 'value' => 'amazing_mugs']]
            ]
        );
    }
}
