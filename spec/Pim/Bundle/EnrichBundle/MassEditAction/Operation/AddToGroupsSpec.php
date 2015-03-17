<?php

namespace spec\Pim\Bundle\EnrichBundle\MassEditAction\Operation;

use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\GroupInterface;
use Prophecy\Argument;

class AddToGroupsSpec extends ObjectBehavior
{
    function it_is_a_mass_edit_operation()
    {
        $this->shouldImplement('Pim\Bundle\EnrichBundle\MassEditAction\Operation\MassEditOperationInterface');
        $this->shouldImplement('Pim\Bundle\EnrichBundle\MassEditAction\Operation\ConfigurableOperationInterface');
        $this->shouldImplement('Pim\Bundle\EnrichBundle\MassEditAction\Operation\BatchableOperationInterface');
    }

    function it_stores_the_groups_to_add_the_products_to(
        ArrayCollection $collection,
        GroupInterface $office,
        GroupInterface $bedroom
    ) {
        $this->getGroups()->shouldReturnAnInstanceOf('Doctrine\Common\Collections\ArrayCollection');

        $collection->add($office);
        $collection->add($bedroom);

        $this->setGroups($collection);

        $this->getGroups()->shouldReturn($collection);
    }

    function it_provides_a_form_type()
    {
        $this->getFormType()->shouldReturn('pim_enrich_mass_add_to_groups');
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
        $this->getAlias()->shouldReturn('add-to-groups');
    }

    function it_provides_correct_actions_to_apply_on_products(
        GroupInterface $office,
        GroupInterface $bedroom,
        ArrayCollection $collection
    ) {
        $office->getCode()->willReturn('office_room');
        $bedroom->getCode()->willReturn('bedroom');

        $collection->add($office);
        $collection->add($bedroom);

        $this->setGroups($collection);
        $collection->map(Argument::type('closure'))->willReturn($collection);
        $collection->toArray()->willReturn(['office_room', 'bedroom']);

        $this->getActions()->shouldReturn(
            [
                [
                    'field' => 'groups',
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
        GroupInterface $office,
        GroupInterface $bedroom,
        ArrayCollection $collection
    ) {
        $office->getCode()->willReturn('office_room');
        $bedroom->getCode()->willReturn('bedroom');

        $collection->add($office);
        $collection->add($bedroom);

        $this->setGroups($collection);
        $collection->map(Argument::type('closure'))->willReturn($collection);
        $collection->toArray()->willReturn(['office_room', 'bedroom']);

        $this->setFilters([
            ['id', 'IN', ['22', '7']]
        ]);

        $this->getBatchConfig()->shouldReturn(
            '{\"filters\":[[\"id\",\"IN\",[\"22\",\"7\"]]],\"actions\":[{\"field\":\"groups\",\"value\":[\"office_room\",\"bedroom\"]}]}'
        );
    }
}
