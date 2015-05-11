<?php

namespace spec\PimEnterprise\Bundle\EnrichBundle\MassEditAction\Operation;

use PhpSpec\ObjectBehavior;

class UnpublishSpec extends ObjectBehavior
{
    function it_is_a_mass_edit_operation()
    {
        $this->shouldImplement('Pim\Bundle\EnrichBundle\MassEditAction\Operation\MassEditOperationInterface');
        $this->shouldImplement('Pim\Bundle\EnrichBundle\MassEditAction\Operation\ConfigurableOperationInterface');
        $this->shouldImplement('Pim\Bundle\EnrichBundle\MassEditAction\Operation\BatchableOperationInterface');
    }

    function it_provides_a_form_type()
    {
        $this->getFormType()->shouldReturn('pimee_enrich_mass_unpublish');
    }

    function it_provides_form_options()
    {
        $this->getFormOptions()->shouldReturn([]);
    }

    function it_provides_items_name_it_works_on()
    {
        $this->getItemsName()->shouldReturn('published_product');
    }

    function it_provides_an_alias()
    {
        $this->getOperationAlias()->shouldReturn('unpublish');
    }

    function it_provides_correct_actions_to_apply_on_products()
    {
        $this->getActions()->shouldReturn([]);
    }

    function it_provides_a_batch_job_code()
    {
        $this->getBatchJobCode()->shouldReturn('unpublish_product');
    }

    function it_provides_formatted_batch_config_for_the_job()
    {
        $this->setFilters([
            ['id', 'IN', ['100', '50']]
        ]);

        $this->getBatchConfig()->shouldReturn(
            '{\"filters\":[[\"id\",\"IN\",[\"100\",\"50\"]]],\"actions\":[]}'
        );
    }
}
