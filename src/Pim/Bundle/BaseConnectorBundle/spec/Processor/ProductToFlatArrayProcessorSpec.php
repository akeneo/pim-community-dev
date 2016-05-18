<?php

namespace spec\Pim\Bundle\BaseConnectorBundle\Processor;

use Akeneo\Component\Batch\Job\JobParameters;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\FileStorage\Model\FileInfoInterface;
use Doctrine\Common\Collections\ArrayCollection;
use League\Flysystem\Filesystem;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Builder\ProductBuilderInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Pim\Component\Catalog\Repository\ChannelRepositoryInterface;
use Prophecy\Argument;
use Symfony\Component\Serializer\Serializer;

class ProductToFlatArrayProcessorSpec extends ObjectBehavior
{
    function let(
        Serializer $serializer,
        ChannelRepositoryInterface $channelRepository,
        ProductBuilderInterface $productBuilder,
        StepExecution $stepExecution
    ) {
        $this->beConstructedWith(
            $serializer,
            $channelRepository,
            $productBuilder,
            ['pim_catalog_file', 'pim_catalog_image']
        );
        $this->setStepExecution($stepExecution);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('\Pim\Bundle\BaseConnectorBundle\Processor\ProductToFlatArrayProcessor');
    }

    function it_is_an_item_processor()
    {
        $this->shouldImplement('\Akeneo\Component\Batch\Item\ItemProcessorInterface');
    }

    function it_returns_flat_data_with_media(
        $channelRepository,
        $serializer,
        $productBuilder,
        $stepExecution,
        Filesystem $filesystem,
        ChannelInterface $channel,
        LocaleInterface $locale,
        ProductInterface $product,
        FileInfoInterface $media1,
        FileInfoInterface $media2,
        ProductValueInterface $value1,
        ProductValueInterface $value2,
        AttributeInterface $attribute,
        ProductValueInterface $identifierValue,
        AttributeInterface $identifierAttribute,
        JobParameters $jobParameters
    ) {
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('channel')->willReturn('foobar');
        $jobParameters->get('decimalSeparator')->willReturn('.');
        $jobParameters->get('dateFormat')->willReturn('yyyy-MM-dd');

        $localeCodes = ['en_US'];

        $channel->getCode()->willReturn('foobar');
        $channel->getLocales()->willReturn(new ArrayCollection([$locale]));
        $channel->getLocaleCodes()->willReturn($localeCodes);
        $productBuilder->addMissingProductValues($product, [$channel], [$locale])->shouldBeCalled();

        $media1->getKey()->willReturn('key/to/media1.jpg');
        $media2->getKey()->willReturn('key/to/media2.jpg');

        $value1->getAttribute()->willReturn($attribute);
        $value1->getMedia()->willReturn($media1);
        $value2->getAttribute()->willReturn($attribute);
        $value2->getMedia()->willReturn($media2);
        $attribute->getAttributeType()->willReturn('pim_catalog_image');
        $product->getValues()->willReturn([$value1, $value2, $identifierValue]);

        $identifierValue->getAttribute()->willReturn($identifierAttribute);
        $identifierAttribute->getAttributeType()->willReturn('pim_catalog_identifier');
        $product->getIdentifier()->willReturn($identifierValue);
        $identifierValue->getData()->willReturn('data');

        $filesystem->has('key/to/media1.jpg')->willReturn(true);
        $filesystem->has('key/to/media2.jpg')->willReturn(true);

        $serializer
            ->normalize($media1, 'flat', ['field_name' => 'media', 'prepare_copy' => true, 'value' => $value1])
            ->willReturn(['normalized_media1']);
        $serializer
            ->normalize($media2, 'flat', ['field_name' => 'media', 'prepare_copy' => true, 'value' => $value2])
            ->willReturn(['normalized_media2']);
        $serializer
            ->normalize(
                $product,
                'flat',
                [
                    'scopeCode'         => 'foobar',
                    'localeCodes'       => $localeCodes,
                    'decimal_separator' => '.',
                    'date_format'       => 'yyyy-MM-dd',
                ]
            )
            ->willReturn(['normalized_product']);

        $channelRepository->findOneByIdentifier('foobar')->willReturn($channel);

        $this->process($product)->shouldReturn(
            [
                'media'   => [['normalized_media1'], ['normalized_media2']],
                'product' => ['normalized_product']
            ]
        );
    }

    function it_returns_flat_data_without_media(
        $productBuilder,
        $stepExecution,
        ChannelInterface $channel,
        LocaleInterface $locale,
        ChannelRepositoryInterface $channelRepository,
        ProductInterface $product,
        Serializer $serializer,
        JobParameters $jobParameters
    ) {
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('channel')->willReturn('foobar');
        $jobParameters->get('decimalSeparator')->willReturn(',');
        $jobParameters->get('dateFormat')->willReturn('yyyy-MM-dd');

        $localeCodes = ['en_US'];

        $channel->getCode()->willReturn('foobar');
        $channel->getLocales()->willReturn(new ArrayCollection([$locale]));
        $channel->getLocaleCodes()->willReturn($localeCodes);
        $productBuilder->addMissingProductValues($product, [$channel], [$locale])->shouldBeCalled();

        $product->getValues()->willReturn([]);

        $serializer
            ->normalize(
                $product,
                'flat',
                [
                    'scopeCode' => 'foobar',
                    'localeCodes' => $localeCodes,
                    'decimal_separator' => ',',
                    'date_format'       => 'yyyy-MM-dd',
                ]
            )
            ->willReturn(['normalized_product']);

        $channelRepository->findOneByIdentifier('foobar')->willReturn($channel);

        $this->process($product)->shouldReturn(['media' => [], 'product' => ['normalized_product']]);
    }
}
