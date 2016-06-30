<?php

namespace spec\Pim\Bundle\EnrichBundle\MassEditAction\Operation;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\CategoryInterface;

class ClassifySpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('add_product_value', 'product', 'pim_enrich_mass_classify');
    }

    function it_is_a_mass_edit_operation()
    {
        $this->shouldImplement('Pim\Bundle\EnrichBundle\MassEditAction\Operation\MassEditOperationInterface');
        $this->shouldImplement('Pim\Bundle\EnrichBundle\MassEditAction\Operation\ConfigurableOperationInterface');
        $this->shouldImplement('Pim\Bundle\EnrichBundle\MassEditAction\Operation\BatchableOperationInterface');
    }

    function it_stores_the_categories_to_add_the_products_to(
        CategoryInterface $officeCategory,
        CategoryInterface $bedroomCategory
    ) {
        $this->getCategories()->shouldReturn([]);

        $this->setCategories([$officeCategory, $bedroomCategory]);

        $this->getCategories()->shouldReturn([$officeCategory, $bedroomCategory]);
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

    function it_provides_an_alias()
    {
        $this->getOperationAlias()->shouldReturn('classify');
    }

    function it_provides_correct_actions_to_apply_on_products(
        CategoryInterface $officeCategory,
        CategoryInterface $bedroomCategory
    ) {
        $officeCategory->getCode()->willReturn('office_room');
        $bedroomCategory->getCode()->willReturn('bedroom');

        $this->setCategories([$officeCategory, $bedroomCategory]);

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
        $this->getJobInstanceCode()->shouldReturn('add_product_value');
    }

    function it_provides_formatted_batch_config_for_the_job(
        CategoryInterface $officeCategory,
        CategoryInterface $bedroomCategory
    ) {
        $officeCategory->getCode()->willReturn('office_room');
        $bedroomCategory->getCode()->willReturn('bedroom');

        $this->setCategories([$officeCategory, $bedroomCategory]);

        $this->setFilters([
            ['id', 'IN', ['49', '2']]
        ]);

        $this->getBatchConfig()->shouldReturn(
            [
                'filters' => [['id', 'IN', ['49', '2']]],
                'actions' => [['field' => 'categories', 'value' => ['office_room', 'bedroom']]]
            ]
        );
    }
}
