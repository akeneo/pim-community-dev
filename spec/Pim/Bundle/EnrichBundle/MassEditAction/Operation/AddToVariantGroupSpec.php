<?php

namespace spec\Pim\Bundle\EnrichBundle\MassEditAction\Operation;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\GroupInterface;

class AddToVariantGroupSpec extends ObjectBehavior
{
    function it_is_a_mass_edit_action()
    {
        $this->shouldImplement('Pim\Bundle\EnrichBundle\MassEditAction\Operation\MassEditOperationInterface');
        $this->shouldImplement('Pim\Bundle\EnrichBundle\MassEditAction\Operation\ConfigurableOperationInterface');
        $this->shouldImplement('Pim\Bundle\EnrichBundle\MassEditAction\Operation\BatchableOperationInterface');
    }

    function it_provides_a_form_type()
    {
        $this->getFormType()->shouldReturn('pim_enrich_mass_add_to_variant_group');
    }

    function it_provides_an_alias()
    {
        $this->getOperationAlias()->shouldReturn('add-to-variant-group');
    }

    function it_provides_form_options()
    {
        $this->getFormOptions()->shouldReturn([]);
    }

    function it_provides_items_name_it_works_on()
    {
        $this->getItemsName()->shouldReturn('product');
    }

    function it_provides_correct_actions_to_apply_on_products(GroupInterface $oroTshirt)
    {
        $oroTshirt->getCode()->willReturn('oro_tshirt');
        $this->setGroup($oroTshirt);

        $this->getActions()->shouldReturn(
            [
                'field' => 'variant_group',
                'value' => 'oro_tshirt',
            ]
        );
    }

    function it_provides_a_batch_job_code()
    {
        $this->getBatchJobCode()->shouldReturn('add_to_variant_group');
    }

    function it_provides_formatted_batch_config_for_the_job(GroupInterface $oroTshirt)
    {
        $oroTshirt->getCode()->willReturn('oro_tshirt');

        $this->setGroup($oroTshirt);

        $this->setFilters([
            ['id', 'IN', ['22', '7']]
        ]);

        $this->getBatchConfig()->shouldReturn(
            '{\"filters\":[[\"id\",\"IN\",[\"22\",\"7\"]]],\"actions\":{\"field\":\"variant_group\",\"value\":\"oro_tshirt\"}}'
        );
    }
}
