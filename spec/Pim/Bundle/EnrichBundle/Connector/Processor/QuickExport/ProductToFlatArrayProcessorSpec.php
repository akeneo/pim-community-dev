<?php

namespace spec\Pim\Bundle\EnrichBundle\Connector\Processor\QuickExport;

use Akeneo\Bundle\BatchBundle\Entity\JobExecution;
use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\BatchBundle\Item\InvalidItemException;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Exception\InvalidArgumentException;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ChannelInterface;
use Pim\Bundle\CatalogBundle\Model\MetricInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\ProductMediaInterface;
use Pim\Bundle\CatalogBundle\Model\ProductPriceInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Component\Connector\Model\JobConfigurationInterface;
use Pim\Component\Connector\Repository\JobConfigurationRepositoryInterface;
use Pim\Component\Localization\Provider\Format\FormatProviderInterface;
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
        FormatProviderInterface $dateformatProvider,
        FormatProviderInterface $numberFormatProvider
    ) {
        $this->beConstructedWith(
            $jobConfigurationRepo,
            $serializer,
            $channelManager,
            $dateformatProvider,
            $numberFormatProvider,
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
        $serializer,
        $channelManager,
        $dateformatProvider,
        $numberFormatProvider,
        ChannelInterface $channel,
        ProductInterface $product,
        ProductMediaInterface $media1,
        ProductMediaInterface $media2,
        ProductValueInterface $value1,
        ProductValueInterface $value2,
        AttributeInterface $attribute
    ) {
        $dateformatProvider->getFormat('en_US')->willReturn('n/j/y');
        $numberFormatProvider->getFormat('en_US')->willReturn(['decimal_separator' => '.']);
        $this->configureOptions('en_US');

        $media1->getFilename()->willReturn('media_name');
        $media1->getOriginalFilename()->willReturn('media_original_name');

        $media2->getFilename()->willReturn('media_name');
        $media2->getOriginalFilename()->willReturn('media_original_name');

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
                    'scopeCode'         => 'mobile',
                    'localeCodes'       => '',
                    'decimal_separator' => '.',
                    'date_format'       => 'n/j/y',
                ]
            )
            ->willReturn(['normalized_product']);

        $channelManager->getChannelByCode('mobile')->willReturn($channel);

        $this->setChannelCode('mobile');
        $this->process($product)->shouldReturn(
            [
                'media'   => ['normalized_media1', 'normalized_media2'],
                'product' => ['normalized_product']
            ]
        );
    }

    function it_returns_flat_data_without_media(
        $dateformatProvider,
        $numberFormatProvider,
        ChannelInterface $channel,
        ChannelManager $channelManager,
        ProductInterface $product,
        Serializer $serializer
    ) {
        $dateformatProvider->getFormat('en_US')->willReturn('n/j/y');
        $numberFormatProvider->getFormat('en_US')->willReturn(['decimal_separator' => '.']);
        $this->configureOptions('en_US');
        $product->getValues()->willReturn([]);

        $serializer
            ->normalize($product, 'flat',
                [
                    'scopeCode'         => 'mobile',
                    'localeCodes'       => '',
                    'decimal_separator' => '.',
                    'date_format'       => 'n/j/y',
                ]
            )
            ->willReturn(['normalized_product']);

        $channelManager->getChannelByCode('mobile')->willReturn($channel);

        $this->setChannelCode('mobile');
        $this->process($product)->shouldReturn(['media' => [], 'product' => ['normalized_product']]);
    }

    function it_throws_an_exception_if_something_goes_wrong_with_media_normalization(
        $serializer,
        ProductInterface $product,
        ProductMediaInterface $media,
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
        $dateformatProvider,
        $numberFormatProvider,
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
        $dateformatProvider->getFormat('en_US')->willReturn('n/j/y');
        $numberFormatProvider->getFormat('en_US')->willReturn(['decimal_separator' => '.']);
        $this->configureOptions('en_US');

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
                    'scopeCode'         => 'mobile',
                    'localeCodes'       => '',
                    'decimal_separator' => '.',
                    'date_format'       => 'n/j/y',
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
        $dateformatProvider,
        $numberFormatProvider,
        ChannelInterface $channel,
        ProductInterface $product,
        ProductValueInterface $number,
        AttributeInterface $attribute,
        MetricInterface $metric,
        ProductValueInterface $metricValue,
        ProductPriceInterface $price,
        ProductValueInterface $priceValue
    ) {
        $dateformatProvider->getFormat('fr_FR')->willReturn('d/m/Y');
        $numberFormatProvider->getFormat('fr_FR')->willReturn(['decimal_separator' => ',']);
        $this->configureOptions('fr_FR');

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
                    'scopeCode'         => 'mobile',
                    'localeCodes'       => '',
                    'decimal_separator' => ',',
                    'date_format'       => 'd/m/Y'
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
