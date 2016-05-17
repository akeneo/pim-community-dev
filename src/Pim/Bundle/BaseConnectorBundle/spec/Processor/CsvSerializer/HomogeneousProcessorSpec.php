<?php

namespace spec\Pim\Bundle\BaseConnectorBundle\Processor\CsvSerializer;

use Akeneo\Component\Batch\Job\JobParameters;
use Akeneo\Component\Batch\Model\StepExecution;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;
use Prophecy\Argument;
use Symfony\Component\Serializer\SerializerInterface;

class HomogeneousProcessorSpec extends ObjectBehavior
{
    function let(
        SerializerInterface $serializer,
        LocaleRepositoryInterface $localeRepository,
        StepExecution $stepExecution
    ) {
        $this->beConstructedWith($serializer, $localeRepository);
        $this->setStepExecution($stepExecution);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('\Pim\Bundle\BaseConnectorBundle\Processor\CsvSerializer\HomogeneousProcessor');
    }

    function it_is_an_item_processor()
    {
        $this->shouldHaveType('\Akeneo\Component\Batch\Item\ItemProcessorInterface');
    }

    function it_is_step_execution_aware()
    {
        $this->shouldHaveType('\Akeneo\Component\Batch\Step\StepExecutionAwareInterface');
    }

    function it_processes_homogeneous_items(
        $serializer,
        $localeRepository,
        $stepExecution,
        JobParameters $jobParameters
    ) {
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $items = [['item1' => ['attr10']], ['item2' => 'attr20'], ['item3' => ['attr30']]];

        $localeRepository->getActivatedLocaleCodes()->willReturn(['code1', 'code2']);
        $serializer->serialize(Argument::cetera())->willReturn('those;items;in;csv;format;');

        $this->process($items)->shouldReturn('those;items;in;csv;format;');
    }
}
