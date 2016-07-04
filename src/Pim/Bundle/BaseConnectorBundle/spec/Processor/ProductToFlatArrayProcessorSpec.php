<?php

namespace spec\Pim\Bundle\BaseConnectorBundle\Processor;

use Akeneo\Component\Batch\Job\JobParameters;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\FileStorage\Model\FileInfoInterface;
use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
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
        StepExecution $stepExecution,
        ObjectDetacherInterface $detacher
    ) {
        $this->beConstructedWith(
            $serializer,
            $channelRepository,
            $productBuilder,
            $detacher,
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
        $detacher,
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
        $filters = [
            'data' => [
                [
                    'field'    => 'enabled',
                    'operator' => '=',
                    'value'    => true
                ]
            ],
            'structure' => [
                'scope'   => 'mobile',
                'locales' => ['fr_FR', 'en_US'],
            ]
        ];
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('filters')->willReturn($filters);
        $jobParameters->get('decimalSeparator')->willReturn('.');
        $jobParameters->get('dateFormat')->willReturn('yyyy-MM-dd');

        $channelRepository->findOneByIdentifier('mobile')->willReturn($channel);
        $channel->getLocales()->willReturn(new ArrayCollection([$locale]));
        $channel->getCode()->willReturn('foobar');
        $channel->getLocaleCodes()->willReturn(['en_US', 'de_DE']);

        $productBuilder->addMissingProductValues($product, [$channel], [$locale])->shouldBeCalled();

        $product->getValues()->willReturn([$value1, $value2, $identifierValue]);

        $value1->getAttribute()->willReturn($attribute);
        $value1->getMedia()->willReturn($media1);

        $value2->getAttribute()->willReturn($attribute);
        $value2->getMedia()->willReturn($media2);

        $identifierValue->getAttribute()->willReturn($identifierAttribute);

        $attribute->getAttributeType()->willReturn('pim_catalog_image');
        $identifierAttribute->getAttributeType()->willReturn('pim_catalog_identifier');

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
                    'localeCodes'       => ['en_US'],
                    'decimal_separator' => '.',
                    'date_format'       => 'yyyy-MM-dd',
                ]
            )
            ->willReturn(['normalized_product']);

        $detacher->detach($product)->shouldBeCalled();

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
        $detacher,
        ChannelInterface $channel,
        LocaleInterface $locale,
        ChannelRepositoryInterface $channelRepository,
        ProductInterface $product,
        Serializer $serializer,
        JobParameters $jobParameters
    ) {
        $filters = [
            'data' => [
                [
                    'field'    => 'enabled',
                    'operator' => '=',
                    'value'    => true
                ]
            ],
            'structure' => [
                'scope'   => 'mobile',
                'locales' => ['fr_FR', 'en_US'],
            ]
        ];

        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('filters')->willReturn($filters);
        $jobParameters->get('decimalSeparator')->willReturn(',');
        $jobParameters->get('dateFormat')->willReturn('yyyy-MM-dd');

        $channel->getCode()->willReturn('mobile');
        $channel->getLocales()->willReturn(new ArrayCollection([$locale]));
        $channel->getLocaleCodes()->willReturn(['en_US']);
        $productBuilder->addMissingProductValues($product, [$channel], [$locale])->shouldBeCalled();
        $product->getValues()->willReturn([]);

        $serializer
            ->normalize(
                $product,
                'flat',
                [
                    'scopeCode'         => 'mobile',
                    'localeCodes'       => ['en_US'],
                    'decimal_separator' => ',',
                    'date_format'       => 'yyyy-MM-dd',
                ]
            )
            ->willReturn(['normalized_product']);

        $channelRepository->findOneByIdentifier('mobile')->willReturn($channel);

        $detacher->detach($product)->shouldBeCalled();

        $this->process($product)->shouldReturn(['media' => [], 'product' => ['normalized_product']]);
    }
}
