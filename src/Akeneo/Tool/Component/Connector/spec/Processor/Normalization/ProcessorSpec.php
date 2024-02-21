<?php

namespace spec\Akeneo\Tool\Component\Connector\Processor\Normalization;

use Akeneo\Tool\Component\Batch\Item\ItemProcessorInterface;
use Akeneo\Tool\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Model\GroupInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProcessorSpec extends ObjectBehavior
{
    function let(NormalizerInterface $normalizer, ObjectDetacherInterface $objectDetacher)
    {
        $this->beConstructedWith($normalizer, $objectDetacher);
    }

    function it_is_a_processor()
    {
        $this->shouldImplement(ItemProcessorInterface::class);
    }

    function it_processes_items($objectDetacher, NormalizerInterface $normalizer, GroupInterface $group)
    {
        $normalizer
            ->normalize($group)
            ->shouldBeCalled()
            ->willReturn([
                'code'   => 'promotion',
                'type'   => 'RELATED',
                'labels' => ['en_US' => 'Promotion', 'de_DE' => 'Förderung']
            ]);

        $this->process($group)->shouldReturn([
            'code'   => 'promotion',
            'type'   => 'RELATED',
            'labels' => ['en_US' => 'Promotion', 'de_DE' => 'Förderung']
        ]);

        $objectDetacher->detach($group)->shouldBeCalled();
    }
}
