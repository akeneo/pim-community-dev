<?php

namespace spec\Pim\Bundle\EnrichBundle\Connector\Processor\QuickExport;

use Akeneo\Bundle\BatchBundle\Entity\JobExecution;
use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\BatchBundle\Item\InvalidItemException;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Exception\InvalidArgumentException;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ChannelInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\ProductMediaInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
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
        StepExecution $stepExecution
    ) {
        $this->beConstructedWith($jobConfigurationRepo, $serializer, $channelManager, 'upload/path/');
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
        $stepExecution,
        $jobConfigurationRepo,
        JobExecution $jobExecution,
        JobConfigurationInterface $jobConfiguration
    ) {
        $this->getChannelCode()->shouldReturn(null);
        $this->setChannelCode('print');
        $this->getChannelCode()->shouldReturn('print');

        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $jobConfigurationRepo->findOneBy(['jobExecution' => $jobExecution])->willReturn($jobConfiguration);
        $jobConfiguration->getConfiguration()->willReturn(
            json_encode(['filters' => [], 'mainContext' => ['scope' => 'ecommerce']])
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

    function it_returns_flat_data_with_media(
        ChannelInterface $channel,
        $channelManager,
        ProductInterface $product,
        ProductMediaInterface $media1,
        ProductMediaInterface $media2,
        ProductValueInterface $value1,
        ProductValueInterface $value2,
        AttributeInterface $attribute,
        $serializer
    ) {
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
            ->normalize($product, 'flat', ['scopeCode' => 'mobile', 'localeCodes' => ''])
            ->willReturn(['normalized_product']);

        $channelManager->getChannelByCode('mobile')->willReturn($channel);

        $this->setChannelCode('mobile');
        $this->process($product)->shouldReturn(['media' => ['normalized_media1', 'normalized_media2'], 'product' => ['normalized_product']]);
    }

    function it_returns_flat_data_without_media(
        ChannelInterface $channel,
        ChannelManager $channelManager,
        ProductInterface $product,
        Serializer $serializer
    ) {
        $product->getValues()->willReturn([]);

        $serializer
            ->normalize($product, 'flat', ['scopeCode' => 'mobile', 'localeCodes' => ''])
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

        $serializer->normalize([$media], Argument::cetera())->willThrow(new FileNotFoundException('upload/path/img.jpg'));

        $this->shouldThrow(
            new InvalidItemException(
                'The file "upload/path/img.jpg" does not exist',
                [ 'item' => 23, 'uploadDirectory' => 'upload/path/']
            )
        )->duringProcess($product);
    }
}
