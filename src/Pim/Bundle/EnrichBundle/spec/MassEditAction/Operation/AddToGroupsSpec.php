<?php

namespace spec\Pim\Bundle\EnrichBundle\MassEditAction\Operation;

use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\EnrichBundle\Form\Type\MassEditAction\AddToGroupsType;
use Pim\Bundle\EnrichBundle\MassEditAction\Operation\BatchableOperationInterface;
use Pim\Bundle\EnrichBundle\MassEditAction\Operation\ConfigurableOperationInterface;
use Pim\Bundle\EnrichBundle\MassEditAction\Operation\MassEditOperationInterface;
use Pim\Component\Catalog\Model\GroupInterface;
use Prophecy\Argument;

class AddToGroupsSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('add_product_value');
    }

    function it_is_a_mass_edit_operation()
    {
        $this->shouldImplement(MassEditOperationInterface::class);
        $this->shouldImplement(ConfigurableOperationInterface::class);
        $this->shouldImplement(BatchableOperationInterface::class);
    }

    function it_stores_the_groups_to_add_the_products_to(
        ArrayCollection $groupCollection,
        GroupInterface $officeGroup,
        GroupInterface $bedroomGroup
    ) {
        $this->getGroups()->shouldReturnAnInstanceOf(ArrayCollection::class);

        $groupCollection->add($officeGroup);
        $groupCollection->add($bedroomGroup);

        $this->setGroups($groupCollection);

        $this->getGroups()->shouldReturn($groupCollection);
    }

    function it_provides_a_form_type()
    {
        $this->getFormType()->shouldReturn(AddToGroupsType::class);
    }

    function it_provides_form_options()
    {
        $this->getFormOptions()->shouldReturn([]);
    }

    function it_provides_an_alias()
    {
        $this->getOperationAlias()->shouldReturn('add-to-groups');
    }

    function it_provides_correct_actions_to_apply_on_products(
        GroupInterface $officeGroup,
        GroupInterface $bedroomGroup,
        ArrayCollection $groupCollection
    ) {
        $officeGroup->getCode()->willReturn('office_room');
        $bedroomGroup->getCode()->willReturn('bedroom');

        $groupCollection->add($officeGroup);
        $groupCollection->add($bedroomGroup);

        $this->setGroups($groupCollection);
        $groupCollection->map(Argument::type('closure'))->willReturn($groupCollection);
        $groupCollection->toArray()->willReturn(['office_room', 'bedroom']);

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
        $this->getJobInstanceCode()->shouldReturn('add_product_value');
    }

    function it_provides_formatted_batch_config_for_the_job(
        GroupInterface $officeGroup,
        GroupInterface $bedroomGroup,
        ArrayCollection $groupCollection
    ) {
        $officeGroup->getCode()->willReturn('office_room');
        $bedroomGroup->getCode()->willReturn('bedroom');

        $groupCollection->add($officeGroup);
        $groupCollection->add($bedroomGroup);

        $this->setGroups($groupCollection);
        $groupCollection->map(Argument::type('closure'))->willReturn($groupCollection);
        $groupCollection->toArray()->willReturn(['office_room', 'bedroom']);

        $this->setFilters([
            ['id', 'IN', ['22', '7']]
        ]);

        $this->getBatchConfig()->shouldReturn(
            [
                'filters' => [['id', 'IN', ['22', '7']]],
                'actions' => [['field' => 'groups', 'value' => ['office_room', 'bedroom']]]
            ]
        );
    }
}
