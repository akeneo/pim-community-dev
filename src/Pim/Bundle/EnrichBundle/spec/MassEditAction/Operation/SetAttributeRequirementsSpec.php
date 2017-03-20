<?php

namespace spec\Pim\Bundle\EnrichBundle\MassEditAction\Operation;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Repository\ChannelRepositoryInterface;

class SetAttributeRequirementsSpec extends ObjectBehavior
{
    function let(
        ChannelRepositoryInterface $channelRepository
    ) {
        $this->beConstructedWith($channelRepository, 'set_attribute_requirements');
    }

    function it_is_a_mass_edit_operation()
    {
        $this->shouldHaveType('Pim\Bundle\EnrichBundle\MassEditAction\Operation\MassEditOperationInterface');
    }

    function it_converts_given_values_to_attributes_and_requirements() {
        $data = "{\"attributes\":[\"foo_attribute\",\"bar_attribute\"]," .
            "\"attribute_requirements\":{\"channel_a\":[\"foo_attribute\"]," .
            "\"channel_b\":[\"bar_attribute\"]}}";

        $this->setValues($data)->getAttributes()->shouldReturn(
            [
                'foo_attribute',
                'bar_attribute',
            ]
        );

        $this->setValues($data)->getRequirements()->shouldReturn(
            [
                'channel_a' => ['foo_attribute'],
                'channel_b' => ['bar_attribute'],
            ]
        );
    }

    function it_uses_the_set_attribute_requirements_form_type()
    {
        $this->getFormType()->shouldReturn('pim_enrich_mass_set_attribute_requirements');
    }

    function it_returns_well_formatted_actions_for_batch_job(
        ChannelRepositoryInterface $channelRepository,
        ChannelInterface $channelA,
        ChannelInterface $channelB
    ) {

        $data = "{\"attributes\":[\"foo_attribute\",\"bar_attribute\"]," .
            "\"attribute_requirements\":{\"channel_a\":[\"foo_attribute\"]," .
            "\"channel_b\":[\"bar_attribute\"]}}";

        $this->channelRepository->findAll()->shouldBeCalled();
        $channelRepository->findAll()->willReturn([$channelA, $channelB]);
        $channelA->getCode()->willReturn('channel_a');
        $channelB->getCode()->willReturn('channel_b');
        $this->channels->willReturn($channelRepository);

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
}
