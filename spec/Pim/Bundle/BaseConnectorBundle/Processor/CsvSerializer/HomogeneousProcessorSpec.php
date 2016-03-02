<?php

namespace spec\Pim\Bundle\BaseConnectorBundle\Processor\CsvSerializer;

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

    function it_processes_homogeneous_items($serializer, $localeRepository)
    {
        $items = [['item1' => ['attr10']], ['item2' => 'attr20'], ['item3' => ['attr30']]];

        $localeRepository->getActivatedLocaleCodes()->willReturn(['code1', 'code2']);
        $serializer->serialize(Argument::cetera())->willReturn('those;items;in;csv;format;');

        $this->process($items)->shouldReturn('those;items;in;csv;format;');
    }
}
