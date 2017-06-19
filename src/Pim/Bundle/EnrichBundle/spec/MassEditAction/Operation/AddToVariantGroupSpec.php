<?php

namespace spec\Pim\Bundle\EnrichBundle\MassEditAction\Operation;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\EnrichBundle\Form\Type\MassEditAction\AddToVariantGroupType;
use Pim\Bundle\EnrichBundle\MassEditAction\Operation\BatchableOperationInterface;
use Pim\Bundle\EnrichBundle\MassEditAction\Operation\ConfigurableOperationInterface;
use Pim\Bundle\EnrichBundle\MassEditAction\Operation\MassEditOperationInterface;
use Pim\Component\Catalog\Model\GroupInterface;

class AddToVariantGroupSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('add_to_variant_group');
    }

    function it_is_a_mass_edit_action()
    {
        $this->shouldImplement(MassEditOperationInterface::class);
        $this->shouldImplement(ConfigurableOperationInterface::class);
        $this->shouldImplement(BatchableOperationInterface::class);
    }

    function it_provides_a_form_type()
    {
        $this->getFormType()->shouldReturn(AddToVariantGroupType::class);
    }

    function it_provides_an_alias()
    {
        $this->getOperationAlias()->shouldReturn('add-to-variant-group');
    }

    function it_provides_form_options()
    {
        $this->getFormOptions()->shouldReturn([]);
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
        $this->getJobInstanceCode()->shouldReturn('add_to_variant_group');
    }

    function it_provides_formatted_batch_config_for_the_job(GroupInterface $oroTshirt)
    {
        $oroTshirt->getCode()->willReturn('oro_tshirt');

        $this->setGroup($oroTshirt);

        $this->setFilters([
            ['id', 'IN', ['22', '7']]
        ]);

        $this->getBatchConfig()->shouldReturn(
            [
                'filters' => [['id', 'IN', ['22', '7']]],
                'actions' => ['field' => 'variant_group', 'value' => 'oro_tshirt']
            ]
        );
    }
}
