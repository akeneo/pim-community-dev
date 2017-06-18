<?php

namespace spec\Pim\Bundle\EnrichBundle\MassEditAction\Operation;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\EnrichBundle\Form\Type\MassEditAction\SetAttributeRequirementsType;
use Pim\Bundle\EnrichBundle\MassEditAction\Operation\MassEditOperationInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Repository\ChannelRepositoryInterface;

class SetAttributeRequirementsSpec extends ObjectBehavior
{
    function let(
        ChannelRepositoryInterface $channelRepository,
        ChannelInterface $channelA,
        ChannelInterface $channelB
    ) {
        $channelA->getCode()->willReturn('channel_a');
        $channelB->getCode()->willReturn('channel_b');
        $channelRepository->findAll()->willReturn([$channelA, $channelB]);
        $this->beConstructedWith($channelRepository, 'set_attribute_requirements');
    }

    function it_is_a_mass_edit_operation()
    {
        $this->shouldHaveType(MassEditOperationInterface::class);
    }

    function it_uses_the_set_attribute_requirements_form_type()
    {
        $this->getFormType()->shouldReturn(SetAttributeRequirementsType::class);
    }

    function it_returns_well_formatted_actions_for_batch_job() {

        $data = $this->getData();

        $this->setValues($data)->getActions()->shouldReturn([
            [
                'attribute_code' => 'foo_attribute',
                'channel_code' => 'channel_a',
                'is_required' => true
            ],
            [
                'attribute_code' => 'foo_attribute',
                'channel_code' => 'channel_b',
                'is_required' => false
            ],
            [
                'attribute_code' => 'bar_attribute',
                'channel_code' => 'channel_a',
                'is_required' => false
            ],
            [
                'attribute_code' => 'bar_attribute',
                'channel_code' => 'channel_b',
                'is_required' => true
            ]
        ]);
    }

    protected function getData()
    {
        return "{\"attributes\":[\"foo_attribute\",\"bar_attribute\"]," .
            "\"attribute_requirements\":{\"channel_a\":[\"foo_attribute\"]," .
            "\"channel_b\":[\"bar_attribute\"]}}";
    }
}
