<?php

namespace spec\Pim\Component\Connector\Processor\Normalization;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\GroupInterface;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class SimpleProcessorSpec extends ObjectBehavior
{
    function let(NormalizerInterface $normalizer)
    {
        $this->beConstructedWith($normalizer);
    }

    function it_is_a_processor()
    {
        $this->shouldImplement('Akeneo\Component\Batch\Item\ItemProcessorInterface');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Connector\Processor\Normalization\SimpleProcessor');
    }

    function it_processes_items(NormalizerInterface $normalizer, GroupInterface $group)
    {
        $normalizer
            ->normalize($group)
            ->shouldBeCalled()
            ->willReturn([
                'code'   => 'promotion',
                'type'   => 'RELATED',
                'labels' => [ 'en_US' => 'Promotion', 'de_DE' => 'Förderung']
            ]);

        $this->process($group)->shouldReturn([
            'code'   => 'promotion',
            'type'   => 'RELATED',
            'labels' => [ 'en_US' => 'Promotion', 'de_DE' => 'Förderung']
        ]);
    }
}
