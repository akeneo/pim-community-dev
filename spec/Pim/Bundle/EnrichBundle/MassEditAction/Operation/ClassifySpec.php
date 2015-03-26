<?php

namespace spec\Pim\Bundle\EnrichBundle\MassEditAction\Operation;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;

class ClassifySpec extends ObjectBehavior
{
    function it_is_a_mass_edit_operation()
    {
        $this->shouldImplement('Pim\Bundle\EnrichBundle\MassEditAction\Operation\MassEditOperationInterface');
        $this->shouldImplement('Pim\Bundle\EnrichBundle\MassEditAction\Operation\ConfigurableOperationInterface');
        $this->shouldImplement('Pim\Bundle\EnrichBundle\MassEditAction\Operation\BatchableOperationInterface');
    }

    function it_stores_the_categories_to_add_the_products_to(
        CategoryInterface $office,
        CategoryInterface $bedroom
    ) {
        $this->getCategories()->shouldReturn([]);

        $this->setCategories([$office, $bedroom]);

        $this->getCategories()->shouldReturn([$office, $bedroom]);
        $this->getCategories()->shouldBeArray();
    }

    function it_provides_a_form_type()
    {
        $this->getFormType()->shouldReturn('pim_enrich_mass_classify');
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
        $this->getAlias()->shouldReturn('classify');
    }

    function it_provides_correct_actions_to_apply_on_products(
        CategoryInterface $office,
        CategoryInterface $bedroom
    ) {
        $office->getCode()->willReturn('office_room');
        $bedroom->getCode()->willReturn('bedroom');

        $this->setCategories([$office, $bedroom]);

        $this->getActions()->shouldReturn(
            [
                [
                    'field' => 'categories',
                    'value' => ['office_room', 'bedroom']
                ]
            ]
        );
    }

    function it_provides_a_batch_job_code()
    {
        $this->getBatchJobCode()->shouldReturn('add_product_value');
    }

    function it_provides_formatted_batch_config_for_the_job(
        CategoryInterface $office,
        CategoryInterface $bedroom
    ) {
        $office->getCode()->willReturn('office_room');
        $bedroom->getCode()->willReturn('bedroom');

        $this->setCategories([$office, $bedroom]);

        $this->setFilters([
            ['id', 'IN', ['49', '2']]
        ]);

        $this->getBatchConfig()->shouldReturn(
            '{\"filters\":[[\"id\",\"IN\",[\"49\",\"2\"]]],\"actions\":[{\"field\":\"categories\",\"value\":[\"office_room\",\"bedroom\"]}]}'
        );
    }
}
