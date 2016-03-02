<?php

namespace spec\Pim\Bundle\EnrichBundle\Connector\Processor\QuickExport;

use Akeneo\Component\Batch\Model\JobExecution;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\Batch\Item\InvalidItemException;
use Akeneo\Component\FileStorage\Model\FileInfoInterface;
use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Builder\ProductBuilderInterface;
use Pim\Component\Catalog\Exception\InvalidArgumentException;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\MetricInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductPriceInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Pim\Component\Connector\Model\JobConfigurationInterface;
use Pim\Component\Connector\Repository\JobConfigurationRepositoryInterface;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\Serializer\Serializer;

class ProductToFlatArrayProcessorSpec extends ObjectBehavior
{
    function let(
        JobConfigurationRepositoryInterface $jobConfigurationRepo,
        Serializer $serializer,
        ChannelManager $channelManager,
        StepExecution $stepExecution,
        ProductBuilderInterface $productBuilder,
        ObjectDetacherInterface $objectDetacher
    ) {
        $this->beConstructedWith(
            $jobConfigurationRepo,
            $serializer,
            $channelManager,
            $productBuilder,
            $objectDetacher,
            'upload/path/'
        );
        $this->setStepExecution($stepExecution);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('\Pim\Bundle\EnrichBundle\Connector\Processor\QuickExport\ProductToFlatArrayProcessor');
    }

    function it_is_a_mass_edit_processor()
    {
        $this->shouldImplement('\Pim\Bundle\EnrichBundle\Connector\Processor\AbstractProcessor');
    }

    function it_is_configurable(
        JobExecution $jobExecution,
        JobConfigurationInterface $jobConfiguration,
        $stepExecution,
        $jobConfigurationRepo
    ) {
        $this->getChannelCode()->shouldReturn(null);
        $this->setChannelCode('print');
        $this->getChannelCode()->shouldReturn('print');

        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $jobConfigurationRepo->findOneBy(['jobExecution' => $jobExecution])->willReturn($jobConfiguration);
        $jobConfiguration->getConfiguration()->willReturn(
            json_encode(['filters' => [], 'mainContext' => ['scope' => 'ecommerce', 'ui_locale' => 'en_US']])
        );

        $this->initialize();
        $this->getChannelCode()->shouldReturn('ecommerce');
    }

    function it_returns_the_configuration_fields()
    {
        $this->getConfigurationFields()->shouldReturn([]);
    }

    function it_throw_an_exception_if_there_is_no_channel(
        $stepExecution,
        $jobConfigurationRepo,
        JobExecution $jobExecution,
        JobConfigurationInterface $jobConfiguration
    ) {
        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $jobConfigurationRepo->findOneBy(['jobExecution' => $jobExecution])->willReturn($jobConfiguration);
        $jobConfiguration->getConfiguration()->willReturn(
            json_encode(['filters' => [], 'mainContext' => ['scope' => null]])
        );

        $this->shouldThrow(new InvalidArgumentException('No channel found'))->duringInitialize();
    }

    function it_throw_an_exception_if_there_is_no_ui_locale(
        $stepExecution,
        $jobConfigurationRepo,
        JobExecution $jobExecution,
        JobConfigurationInterface $jobConfiguration
    ) {
        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $jobConfigurationRepo->findOneBy(['jobExecution' => $jobExecution])->willReturn($jobConfiguration);
        $jobConfiguration->getConfiguration()->willReturn(
            json_encode(['filters' => [], 'mainContext' => ['scope' => 'ecommerce']])
        );

        $this->shouldThrow(new InvalidArgumentException('No UI locale found'))->duringInitialize();
    }

    function it_returns_flat_data_with_media(
        $channelManager,
        $serializer,
        $productBuilder,
        $objectDetacher,
        ChannelInterface $channel,
        ProductInterface $product,
        FileInfoInterface $media1,
        FileInfoInterface $media2,
        ProductValueInterface $value1,
        ProductValueInterface $value2,
        AttributeInterface $attribute
    ) {
        $this->setLocale('en_US');

        $productBuilder->addMissingProductValues($product)->shouldBeCalled();

        $value1->getAttribute()->willReturn($attribute);
        $value1->getData()->willReturn($media1);
        $value2->getAttribute()->willReturn($attribute);
        $value2->getData()->willReturn($media2);
        $attribute->getAttributeType()->willReturn('pim_catalog_image');
        $product->getValues()->willReturn([$value1, $value2]);

        $serializer
            ->normalize([$media1, $media2], 'flat', ['field_name' => 'media', 'prepare_copy' => true])
            ->willReturn(['normalized_media1', 'normalized_media2']);

        $serializer
            ->normalize($product, 'flat',
                [
                    'scopeCode'   => 'mobile',
                    'localeCodes' => '',
                    'locale'      => 'en_US',
                ]
            )
            ->willReturn(['normalized_product']);

        $channelManager->getChannelByCode('mobile')->willReturn($channel);

        $this->setChannelCode('mobile');
        $objectDetacher->detach($product)->shouldBeCalled();
    $this->process($product)->shouldReturn(
        [
            'media'   => ['normalized_media1', 'normalized_media2'],
            'product' => ['normalized_product']
        ]
    );
    }

    function it_returns_flat_data_without_media(
        $productBuilder,
        $objectDetacher,
        ChannelInterface $channel,
        ChannelManager $channelManager,
        ProductInterface $product,
        Serializer $serializer
    ) {
        $productBuilder->addMissingProductValues($product)->shouldBeCalled();

        $this->setLocale('en_US');
        $product->getValues()->willReturn([]);

        $serializer
            ->normalize($product, 'flat',
                [
                    'scopeCode'   => 'mobile',
                    'localeCodes' => '',
                    'locale'      => 'en_US',
                ]
            )
            ->willReturn(['normalized_product']);

        $channelManager->getChannelByCode('mobile')->willReturn($channel);

        $this->setChannelCode('mobile');
        $objectDetacher->detach($product)->shouldBeCalled();
        $this->process($product)->shouldReturn(['media' => [], 'product' => ['normalized_product']]);
    }

    function it_throws_an_exception_if_something_goes_wrong_with_media_normalization(
        $serializer,
        ProductInterface $product,
        FileInfoInterface $media,
        ProductValueInterface $value,
        ProductValueInterface $value2,
        AttributeInterface $attribute
    ) {
        $product->getValues()->willReturn([$value]);
        $product->getIdentifier()->willReturn($value2);

        $value->getAttribute()->willReturn($attribute);
        $value->getData()->willReturn($media);
        $value2->getData()->willReturn(23);

        $attribute->getAttributeType()->willReturn('pim_catalog_image');

        $serializer->normalize([$media], Argument::cetera())->willThrow(
            new FileNotFoundException('upload/path/img.jpg')
        );

        $this->shouldThrow(
            new InvalidItemException(
                'The file "upload/path/img.jpg" does not exist',
                ['item' => 23, 'uploadDirectory' => 'upload/path/']
            )
        )->duringProcess($product);
    }

    function it_returns_flat_data_with_english_attributes(
        $channelManager,
        $serializer,
        ChannelInterface $channel,
        ProductInterface $product,
        ProductValueInterface $number,
        AttributeInterface $attribute,
        MetricInterface $metric,
        ProductValueInterface $metricValue,
        ProductPriceInterface $price,
        ProductValueInterface $priceValue,
        AttributeInterface $date,
        ProductValueInterface $dateValue
    ) {
        $this->setLocale('en_US');

        $attribute->getAttributeType()->willReturn('pim_catalog_number');
        $number->getDecimal('10.50');
        $number->getAttribute()->willReturn($attribute);

        $attribute->getAttributeType()->willReturn('pim_catalog_metric');
        $metric->getData()->willReturn('10.00');
        $metric->getUnit()->willReturn('GRAM');
        $metricValue->getAttribute()->willReturn($attribute);
        $metricValue->getData()->willReturn($metric);

        $attribute->getAttributeType()->willReturn('pim_catalog_price_collection');
        $price->getData()->willReturn('10');
        $price->getCurrency()->willReturn('EUR');
        $priceValue->getAttribute()->willReturn($attribute);
        $priceValue->getData()->willReturn($price);

        $attribute->getAttributeType()->willReturn('pim_catalog_date');
        $dateValue->getAttribute()->willReturn($date);

        $product->getValues()->willReturn([$number, $metricValue, $priceValue, $dateValue]);

        $serializer
            ->normalize($product, 'flat',
                [
                    'scopeCode'   => 'mobile',
                    'localeCodes' => '',
                    'locale'      => 'en_US',
                ]
            )
            ->willReturn(['10.50', '10.00 GRAM', '10.00 EUR', '10/25/15']);

        $channelManager->getChannelByCode('mobile')->willReturn($channel);

        $this->setChannelCode('mobile');
        $this->process($product)->shouldReturn(
            [
                'media'   => [],
                'product' => ['10.50', '10.00 GRAM', '10.00 EUR', '10/25/15']
            ]
        );
    }

    function it_returns_flat_data_with_french_attribute(
        $channelManager,
        $serializer,
        ChannelInterface $channel,
        ProductInterface $product,
        ProductValueInterface $number,
        AttributeInterface $attribute,
        MetricInterface $metric,
        ProductValueInterface $metricValue,
        ProductPriceInterface $price,
        ProductValueInterface $priceValue
    ) {
        $this->setLocale('fr_FR');

        $attribute->getAttributeType()->willReturn('pim_catalog_number');
        $number->getDecimal('10.50');
        $number->getAttribute()->willReturn($attribute);

        $attribute->getAttributeType()->willReturn('pim_catalog_metric');
        $metric->getData()->willReturn('10.00');
        $metric->getUnit()->willReturn('GRAM');
        $metricValue->getAttribute()->willReturn($attribute);
        $metricValue->getData()->willReturn($metric);

        $attribute->getAttributeType()->willReturn('pim_catalog_price_collection');
        $price->getData()->willReturn('10');
        $price->getCurrency()->willReturn('EUR');
        $priceValue->getAttribute()->willReturn($attribute);
        $priceValue->getData()->willReturn($price);

        $product->getValues()->willReturn([$number, $metricValue, $priceValue]);

        $serializer
            ->normalize($product, 'flat',
                [
                    'scopeCode'   => 'mobile',
                    'localeCodes' => '',
                    'locale'      => 'fr_FR'
                ]
            )
            ->willReturn(['10,50', '10,00 GRAM', '10,00 EUR', '25/10/2015']);

        $channelManager->getChannelByCode('mobile')->willReturn($channel);

        $this->setChannelCode('mobile');
        $this->process($product)->shouldReturn(
            [
                'media'   => [],
                'product' => ['10,50', '10,00 GRAM', '10,00 EUR', '25/10/2015']
            ]
        );
    }
}
