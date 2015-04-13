<?php

namespace spec\Pim\Bundle\EnrichBundle\MassEditAction\Operation;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\FamilyInterface;

class ChangeFamilySpec extends ObjectBehavior
{
    function it_is_a_mass_edit_operation()
    {
        $this->shouldImplement('Pim\Bundle\EnrichBundle\MassEditAction\Operation\MassEditOperationInterface');
        $this->shouldImplement('Pim\Bundle\EnrichBundle\MassEditAction\Operation\ConfigurableOperationInterface');
        $this->shouldImplement('Pim\Bundle\EnrichBundle\MassEditAction\Operation\BatchableOperationInterface');
    }

    function it_stores_the_family_to_add_the_products_to(FamilyInterface $mugs)
    {
        $this->getFamily()->shouldReturn(null);

        $this->setFamily($mugs);

        $this->getFamily()->shouldReturn($mugs);
        $this->getFamily()->shouldBeAnInstanceOf('Pim\Bundle\CatalogBundle\Model\FamilyInterface');
    }

    function it_provides_a_form_type()
    {
        $this->getFormType()->shouldReturn('pim_enrich_mass_change_family');
    }

    function it_provides_form_options()
    {
        $this->getFormOptions()->shouldReturn([]);
    }

    function it_provides_items_name_it_works_on()
    {
        $this->getItemsName()->shouldReturn('product');
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
        $this->getBatchJobCode()->shouldReturn('update_product_value');
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
            '{\"filters\":[[\"id\",\"IN\",[\"1003\",\"1002\"]]],\"actions\":[{\"field\":\"family\",\"value\":\"amazing_mugs\"}]}'
        );
    }
}
