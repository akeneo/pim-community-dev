<?php

namespace spec\Pim\Bundle\BaseConnectorBundle\Processor\CsvSerializer;

use Akeneo\Component\Batch\Model\StepExecution;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;
use Prophecy\Argument;
use Symfony\Component\Serializer\SerializerInterface;

class HeterogeneousProcessorSpec extends ObjectBehavior
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
        $this->shouldHaveType('\Pim\Bundle\BaseConnectorBundle\Processor\CsvSerializer\HeterogeneousProcessor');
    }

    function it_is_an_item_processor()
    {
        $this->shouldHaveType('\Akeneo\Component\Batch\Item\ItemProcessorInterface');
    }

    function it_is_step_execution_aware()
    {
        $this->shouldHaveType('\Akeneo\Component\Batch\Step\StepExecutionAwareInterface');
    }

    function it_provides_configuration_fields()
    {
        $this->getConfigurationFields()->shouldReturn([
            'delimiter' => [
                'options' => [
                    'label' => 'pim_base_connector.export.delimiter.label',
                    'help'  => 'pim_base_connector.export.delimiter.help'
                ]
            ],
            'enclosure' => [
                'options' => [
                    'label' => 'pim_base_connector.export.enclosure.label',
                    'help'  => 'pim_base_connector.export.enclosure.help'
                ]
            ],
            'withHeader' => [
                'type' => 'switch',
                'options' => [
                    'label' => 'pim_base_connector.export.withHeader.label',
                    'help'  => 'pim_base_connector.export.withHeader.help'
                ]
            ]
        ]);
    }

    function it_is_configurable()
    {
        $this->getDelimiter()->shouldReturn(';');
        $this->getEnclosure()->shouldReturn('"');
        $this->isWithHeader()->shouldReturn(true);

        $this->setDelimiter(',');
        $this->setEnclosure('^');
        $this->setWithHeader(false);

        $this->getDelimiter()->shouldReturn(',');
        $this->getEnclosure()->shouldReturn('^');
        $this->isWithHeader()->shouldReturn(false);
    }

    function it_increments_summary_info_including_header($stepExecution, $serializer, $localeRepository)
    {
        $items = [['item1' => ['attr10']], ['item2'], ['item3' => ['attr30', 'attr31']]];

        $stepExecution->addSummaryInfo('write', 2)->shouldBeCalled();

        $localeRepository->getActivatedLocaleCodes()->willReturn([]);
        $serializer->serialize(Argument::cetera())->willReturn('those;items;in;csv;format;');

        $this->process($items);
    }

    function it_increments_summary_info_excluding_header($stepExecution, $serializer, $localeRepository)
    {
        $items = [['item1' => ['attr10']], ['item2'], ['item3' => ['attr30', 'attr31']]];

        $stepExecution->addSummaryInfo('write', 3)->shouldBeCalled();

        $localeRepository->getActivatedLocaleCodes()->willReturn([]);
        $serializer->serialize(Argument::cetera())->willReturn('those;items;in;csv;format;');

        $this->setWithHeader(false);
        $this->process($items);
    }

    function it_processes_an_heterogeneous_item($serializer, $localeRepository)
    {
        $items = [['item1' => ['attr10']], ['item2'], ['item3' => ['attr30', 'attr31']]];

        $localeRepository->getActivatedLocaleCodes()->willReturn([]);
        $serializer->serialize(Argument::cetera())->willReturn('those;items;in;csv;format;');

        $this->process($items)->shouldReturn('those;items;in;csv;format;');
    }
}
