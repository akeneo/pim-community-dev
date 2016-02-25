<?php

namespace spec\Pim\Bundle\BaseConnectorBundle\Processor\CsvSerializer;

use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\FileStorage\Model\FileInfoInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\AttributeType\AttributeTypes;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Pim\Component\Catalog\Repository\ChannelRepositoryInterface;
use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;
use Prophecy\Argument;
use Symfony\Component\Serializer\SerializerInterface;

class ProductProcessorSpec extends ObjectBehavior
{
    function let(
        SerializerInterface $serializer,
        LocaleRepositoryInterface $localeRepository,
        StepExecution $stepExecution,
        ChannelRepositoryInterface $channelRepository
    ) {
        $this->beConstructedWith($serializer, $localeRepository, $channelRepository);
        $this->setStepExecution($stepExecution);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('\Pim\Bundle\BaseConnectorBundle\Processor\CsvSerializer\ProductProcessor');
    }

    function it_is_an_item_processor()
    {
        $this->shouldHaveType('\Akeneo\Component\Batch\Item\ItemProcessorInterface');
    }

    function it_is_step_execution_aware()
    {
        $this->shouldHaveType('\Akeneo\Component\Batch\Step\StepExecutionAwareInterface');
    }

    function it_provides_configuration_fields($channelRepository)
    {
        $channelRepository->getLabelsIndexedByCode()->willReturn(['mobile', 'Magento']);

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
        $channelRepository,
        ProductInterface $product1,
        ProductInterface $product2,
        ChannelInterface $channel
    ) {
        $products = [$product1, $product2];

        $product1->getValues()->willReturn([]);
        $product2->getValues()->willReturn([]);

        $stepExecution->addSummaryInfo('write', 1)->shouldBeCalled();

        $channelRepository->findOneBy(['code' => 'mobile'])->willReturn($channel);
        $channel->getLocaleCodes()->willReturn('en-US');

        $serializer->serialize(Argument::cetera())->willReturn('those;items;in;csv;format;');

        $this->setChannel('mobile');
        $this->setWithHeader(true);
        $this->process($products);
    }

    function it_increments_summary_info_excluding_header(
        $stepExecution,
        $serializer,
        $channelRepository,
        ProductInterface $product1,
        ProductInterface $product2,
        ChannelInterface $channel
    ) {
        $products = [$product1, $product2];

        $product1->getValues()->willReturn([]);
        $product2->getValues()->willReturn([]);

        $stepExecution->addSummaryInfo('write', 2)->shouldBeCalled();

        $channelRepository->findOneBy(['code' => 'mobile'])->willReturn($channel);
        $channel->getLocaleCodes()->willReturn('en-US');

        $serializer->serialize(Argument::cetera())->willReturn('those;items;in;csv;format;');

        $this->setChannel('mobile');
        $this->setWithHeader(false);
        $this->process($products);
    }

    function it_processes_items_with_media(
        $serializer,
        $channelRepository,
        ProductInterface $product1,
        ProductInterface $product2,
        FileInfoInterface $media1,
        FileInfoInterface $media2,
        ChannelInterface $channel,
        ProductValueInterface $value1,
        ProductValueInterface $value2,
        ProductValueInterface $value3,
        AttributeInterface $attribute1,
        AttributeInterface $attribute2,
        AttributeInterface $attribute3
    ) {
        $products = [$product1, $product2];

        $product1->getValues()->willReturn([$value1]);
        $product2->getValues()->willReturn([$value2, $value3]);

        $value1->getAttribute()->willReturn($attribute1);
        $value2->getAttribute()->willReturn($attribute2);
        $value3->getAttribute()->willReturn($attribute3);

        $attribute1->getAttributeType()->willReturn(AttributeTypes::IMAGE);
        $attribute2->getAttributeType()->willReturn(AttributeTypes::FILE);
        $attribute3->getAttributeType()->willReturn(AttributeTypes::TEXT);

        $value1->getData()->willReturn($media1);
        $value2->getData()->willReturn($media2);

        $channelRepository->findOneBy(['code' => 'mobile'])->willReturn($channel);
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
        $channelRepository,
        ProductInterface $product1,
        ProductInterface $product2,
        ChannelInterface $channel
    ) {
        $products = [$product1, $product2];

        $product1->getValues()->willReturn([]);
        $product2->getValues()->willReturn([]);

        $channelRepository->findOneBy(['code' => 'mobile'])->willReturn($channel);
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
        $channelRepository,
        ProductInterface $product,
        ChannelInterface $channel
    ) {
        $product->getValues()->willReturn([]);

        $channelRepository->findOneBy(['code' => 'mobile'])->willReturn($channel);
        $channel->getLocaleCodes()->willReturn('en-US');

        $serializer->serialize(Argument::cetera())->willReturn('those;items;in;csv;format;');

        $this->setChannel('mobile');
        $this->process($product)->shouldReturn([
            'entry' => 'those;items;in;csv;format;',
            'media' => []
        ]);
    }
}
