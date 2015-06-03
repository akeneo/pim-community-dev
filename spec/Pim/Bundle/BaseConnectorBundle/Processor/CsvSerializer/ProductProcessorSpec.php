<?php

namespace spec\Pim\Bundle\BaseConnectorBundle\Processor\CsvSerializer;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Bundle\CatalogBundle\Manager\LocaleManager;
use Pim\Bundle\CatalogBundle\Model\ChannelInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\ProductMediaInterface;
use Prophecy\Argument;
use Symfony\Component\Serializer\SerializerInterface;

class ProductProcessorSpec extends ObjectBehavior
{
    function let(
        SerializerInterface $serializer,
        LocaleManager $localeManager,
        StepExecution $stepExecution,
        ChannelManager $channelManager
    ) {
        $this->beConstructedWith($serializer, $localeManager, $channelManager);
        $this->setStepExecution($stepExecution);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('\Pim\Bundle\BaseConnectorBundle\Processor\CsvSerializer\ProductProcessor');
    }

    function it_is_an_item_processor()
    {
        $this->shouldHaveType('\Akeneo\Bundle\BatchBundle\Item\ItemProcessorInterface');
    }

    function it_is_step_execution_aware()
    {
        $this->shouldHaveType('\Akeneo\Bundle\BatchBundle\Step\StepExecutionAwareInterface');
    }

    function it_provides_configuration_fields($channelManager)
    {
        $channelManager->getChannelChoices()->willReturn(['mobile', 'Magento']);

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
            ],
            'channel' => [
                'type'    => 'choice',
                'options' => [
                    'choices'  => ['mobile', 'Magento'],
                    'required' => true,
                    'select2'  => true,
                    'label'    => 'pim_base_connector.export.channel.label',
                    'help'     => 'pim_base_connector.export.channel.help'
                ]
            ]
        ]);
    }

    function it_is_configurable()
    {
        $this->getDelimiter()->shouldReturn(';');
        $this->getEnclosure()->shouldReturn('"');
        $this->isWithHeader()->shouldReturn(true);
        $this->getChannel()->shouldReturn(null);

        $this->setDelimiter(',');
        $this->setEnclosure('^');
        $this->setWithHeader(false);
        $this->setChannel('mobile');

        $this->getDelimiter()->shouldReturn(',');
        $this->getEnclosure()->shouldReturn('^');
        $this->isWithHeader()->shouldReturn(false);
        $this->getChannel()->shouldReturn('mobile');
    }

    function it_increments_summary_info_including_header(
        $stepExecution,
        $serializer,
        $channelManager,
        ProductInterface $product1,
        ProductInterface $product2,
        ChannelInterface $channel
    ) {
        $products = [$product1, $product2];

        $product1->getMedia()->willReturn([]);
        $product2->getMedia()->willReturn([]);

        $stepExecution->addSummaryInfo('write', 1)->shouldBeCalled();

        $channelManager->getChannelByCode('mobile')->willReturn($channel);
        $channel->getLocaleCodes()->willReturn('en-US');

        $serializer->serialize(Argument::cetera())->willReturn('those;items;in;csv;format;');

        $this->setChannel('mobile');
        $this->setWithHeader(true);
        $this->process($products);
    }

    function it_increments_summary_info_excluding_header(
        $stepExecution,
        $serializer,
        $channelManager,
        ProductInterface $product1,
        ProductInterface $product2,
        ChannelInterface $channel
    ) {
        $products = [$product1, $product2];

        $product1->getMedia()->willReturn([]);
        $product2->getMedia()->willReturn([]);

        $stepExecution->addSummaryInfo('write', 2)->shouldBeCalled();

        $channelManager->getChannelByCode('mobile')->willReturn($channel);
        $channel->getLocaleCodes()->willReturn('en-US');

        $serializer->serialize(Argument::cetera())->willReturn('those;items;in;csv;format;');

        $this->setChannel('mobile');
        $this->setWithHeader(false);
        $this->process($products);
    }

    function it_processes_items_with_media(
        $serializer,
        $channelManager,
        ProductInterface $product1,
        ProductInterface $product2,
        ProductMediaInterface $media1,
        ProductMediaInterface $media2,
        ChannelInterface $channel
    ) {
        $products = [$product1, $product2];

        $product1->getMedia()->willReturn([$media1]);
        $product2->getMedia()->willReturn([$media2]);

        $channelManager->getChannelByCode('mobile')->willReturn($channel);
        $channel->getLocaleCodes()->willReturn('en-US');

        $serializer->serialize(Argument::cetera())->willReturn('those;items;in;csv;format;');

        $this->setChannel('mobile');
        $this->process($products)->shouldReturn([
            'entry' => 'those;items;in;csv;format;',
            'media' => [$media2, $media1]
        ]);
    }

    function it_processes_items_without_media(
        $serializer,
        $channelManager,
        ProductInterface $product1,
        ProductInterface $product2,
        ChannelInterface $channel
    ) {
        $products = [$product1, $product2];

        $product1->getMedia()->willReturn([]);
        $product2->getMedia()->willReturn([]);

        $channelManager->getChannelByCode('mobile')->willReturn($channel);
        $channel->getLocaleCodes()->willReturn('en-US');

        $serializer->serialize(Argument::cetera())->willReturn('those;items;in;csv;format;');

        $this->setChannel('mobile');
        $this->process($products)->shouldReturn([
            'entry' => 'those;items;in;csv;format;',
            'media' => []
        ]);
    }

    function it_processes_items_even_if_it_is_not_an_array(
        $serializer,
        $channelManager,
        ProductInterface $product,
        ChannelInterface $channel
    ) {
        $product->getMedia()->willReturn([]);

        $channelManager->getChannelByCode('mobile')->willReturn($channel);
        $channel->getLocaleCodes()->willReturn('en-US');

        $serializer->serialize(Argument::cetera())->willReturn('those;items;in;csv;format;');

        $this->setChannel('mobile');
        $this->process($product)->shouldReturn([
            'entry' => 'those;items;in;csv;format;',
            'media' => []
        ]);
    }
}
