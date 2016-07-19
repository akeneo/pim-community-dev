<?php

namespace spec\Pim\Bundle\EnrichBundle\Connector\Processor\QuickExport;

use Akeneo\Component\Batch\Item\FileInvalidItem;
use Akeneo\Component\Batch\Item\InvalidItemInterface;
use Akeneo\Component\Batch\Job\JobParameters;
use Akeneo\Component\Batch\Model\JobExecution;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\Batch\Item\InvalidItemException;
use Akeneo\Component\FileStorage\Model\FileInfoInterface;
use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\UserBundle\Entity\UserInterface;
use Pim\Component\Catalog\Builder\ProductBuilderInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\MetricInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductPriceInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Pim\Component\Catalog\Repository\ChannelRepositoryInterface;
use Pim\Component\Connector\ArrayConverter\FlatToStandard\Product\FieldSplitter;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Serializer\Serializer;

class ProductToFlatArrayProcessorSpec extends ObjectBehavior
{
    function let(
        Serializer $serializer,
        ChannelRepositoryInterface $channelRepository,
        StepExecution $stepExecution,
        ProductBuilderInterface $productBuilder,
        ObjectDetacherInterface $objectDetacher,
        UserProviderInterface $userProvider,
        TokenStorageInterface $tokenStorage,
        FieldSplitter $fieldSplitter,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->beConstructedWith(
            $serializer,
            $channelRepository,
            $productBuilder,
            $objectDetacher,
            $userProvider,
            $tokenStorage,
            $fieldSplitter,
            $attributeRepository,
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
        $stepExecution,
        JobParameters $jobParameters
    ) {
        $configuration = [
            'filters'             => [],
            'mainContext'         => [
                'scope'     => 'ecommerce',
                'ui_locale' => 'en_US',
            ],
            'selected_properties' => [],
        ];
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('filters')->willReturn($configuration['filters']);
        $jobParameters->get('mainContext')->willReturn($configuration['mainContext']);
        $jobParameters->get('selected_properties')->willReturn($configuration['selected_properties']);

        $stepExecution->getJobExecution()->willReturn($jobExecution);

        $this->initialize();
    }

    function it_returns_flat_data_with_media(
        $channelRepository,
        $serializer,
        $productBuilder,
        $objectDetacher,
        $userProvider,
        $stepExecution,
        $attributeRepository,
        JobExecution $jobExecution,
        UserInterface $user,
        ChannelInterface $channel,
        ProductInterface $product,
        FileInfoInterface $media1,
        FileInfoInterface $media2,
        ProductValueInterface $value1,
        ProductValueInterface $value2,
        AttributeInterface $attribute,
        JobParameters $jobParameters
    ) {
        $configuration = [
            'filters'             => [],
            'mainContext'         => [
                'scope'     => 'mobile',
                'ui_locale' => 'en_US',
            ],
            'selected_properties' => null,
        ];
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('filters')->willReturn($configuration['filters']);
        $jobParameters->get('mainContext')->willReturn($configuration['mainContext']);
        $jobParameters->get('selected_properties')->willReturn($configuration['selected_properties']);

        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $jobExecution->getUser()->willReturn('michel');
        $userProvider->loadUserByUsername('michel')->willReturn($user);
        $user->getRoles()->willReturn(['ROLE_MICHEL']);

        $productBuilder->addMissingProductValues($product)->shouldBeCalled();

        $value1->getAttribute()->willReturn($attribute);
        $value1->getData()->willReturn($media1);
        $value2->getAttribute()->willReturn($attribute);
        $value2->getData()->willReturn($media2);
        $attribute->getAttributeType()->willReturn('pim_catalog_image');
        $product->getValues()->willReturn([$value1, $value2]);

        $attributeRepository->getIdentifierCode()->willReturn('sku');

        $serializer
            ->normalize([$media1, $media2], 'flat', ['field_name' => 'media', 'prepare_copy' => true])
            ->willReturn(['normalized_media1', 'normalized_media2']);

        $serializer
            ->normalize(
                $product,
                'flat',
                [
                    'scopeCode'    => 'mobile',
                    'localeCodes'  => '',
                    'locale'       => 'en_US',
                    'filter_types' => [
                        'pim.transform.product_value.flat',
                        'pim.transform.product_value.flat.quick_export'
                    ],
                    'selected_properties' => null,
                ]
            )
            ->willReturn(['normalized_product']);

        $channelRepository->findOneByIdentifier('mobile')->willReturn($channel);

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
        $userProvider,
        $stepExecution,
        $attributeRepository,
        JobExecution $jobExecution,
        UserInterface $user,
        ChannelInterface $channel,
        ChannelRepositoryInterface $channelRepository,
        ProductInterface $product,
        Serializer $serializer,
        JobParameters $jobParameters
    ) {
        $configuration = [
            'filters'     => [],
            'mainContext' => [
                'scope'     => 'mobile',
                'ui_locale' => 'en_US',
            ],
            'selected_properties'     => null,
        ];
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('filters')->willReturn($configuration['filters']);
        $jobParameters->get('mainContext')->willReturn($configuration['mainContext']);
        $jobParameters->get('selected_properties')->willReturn($configuration['selected_properties']);

        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $jobExecution->getUser()->willReturn('michel');
        $userProvider->loadUserByUsername('michel')->willReturn($user);
        $user->getRoles()->willReturn(['ROLE_MICHEL']);

        $productBuilder->addMissingProductValues($product)->shouldBeCalled();

        $product->getValues()->willReturn([]);

        $attributeRepository->getIdentifierCode()->willReturn('sku');

        $serializer
            ->normalize(
                $product,
                'flat',
                [
                    'scopeCode'   => 'mobile',
                    'localeCodes' => '',
                    'locale'      => 'en_US',
                    'filter_types' => [
                        'pim.transform.product_value.flat',
                        'pim.transform.product_value.flat.quick_export'
                    ],
                    'selected_properties' => null,
                ]
            )
            ->willReturn(['normalized_product']);

        $channelRepository->findOneByIdentifier('mobile')->willReturn($channel);

        $objectDetacher->detach($product)->shouldBeCalled();
        $this->process($product)->shouldReturn(['media' => [], 'product' => ['normalized_product']]);
    }

    function it_throws_an_exception_if_something_goes_wrong_with_media_normalization(
        $serializer,
        $userProvider,
        $stepExecution,
        $attributeRepository,
        JobExecution $jobExecution,
        UserInterface $user,
        ProductInterface $product,
        FileInfoInterface $media,
        ProductValueInterface $value,
        ProductValueInterface $value2,
        AttributeInterface $attribute,
        JobParameters $jobParameters,
        InvalidItemInterface $invalidItem
    ) {
        $configuration = [
            'filters'     => [],
            'mainContext' => [
                'scope'     => 'mobile',
                'ui_locale' => 'en_US',
            ],
            'selected_properties'     => null,
        ];
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('filters')->willReturn($configuration['filters']);
        $jobParameters->get('mainContext')->willReturn($configuration['mainContext']);
        $jobParameters->get('selected_properties')->willReturn($configuration['selected_properties']);

        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $jobExecution->getUser()->willReturn('michel');
        $userProvider->loadUserByUsername('michel')->willReturn($user);
        $user->getRoles()->willReturn(['ROLE_MICHEL']);

        $product->getValues()->willReturn([$value]);
        $product->getIdentifier()->willReturn($value2);

        $value->getAttribute()->willReturn($attribute);
        $value->getData()->willReturn($media);
        $value2->getData()->willReturn(23);

        $attribute->getAttributeType()->willReturn('pim_catalog_image');

        $attributeRepository->getIdentifierCode()->willReturn('sku');

        $serializer->normalize([$media], Argument::cetera())->willThrow(
            new FileNotFoundException('upload/path/img.jpg')
        );

        $invalidItem->getInvalidData()->willReturn(['item' => 23, 'uploadDirectory' => 'upload/path/']);

        $this->shouldThrow('Akeneo\Component\Batch\Item\InvalidItemException')->duringProcess($product);
    }

    function it_returns_flat_data_with_english_attributes(
        $channelRepository,
        $serializer,
        $userProvider,
        $stepExecution,
        JobExecution $jobExecution,
        UserInterface $user,
        ChannelInterface $channel,
        ProductInterface $product,
        ProductValueInterface $number,
        AttributeInterface $attribute,
        MetricInterface $metric,
        ProductValueInterface $metricValue,
        ProductPriceInterface $price,
        ProductValueInterface $priceValue,
        AttributeInterface $date,
        ProductValueInterface $dateValue,
        JobParameters $jobParameters
    ) {
        $configuration = [
            'filters'     => [],
            'mainContext' => [
                'scope'     => 'mobile',
                'ui_locale' => 'en_US',
            ],
            'selected_properties'     => null,
        ];

        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('filters')->willReturn($configuration['filters']);
        $jobParameters->get('mainContext')->willReturn($configuration['mainContext']);
        $jobParameters->get('selected_properties')->willReturn($configuration['selected_properties']);

        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $jobExecution->getUser()->willReturn('michel');
        $userProvider->loadUserByUsername('michel')->willReturn($user);
        $user->getRoles()->willReturn(['ROLE_MICHEL']);

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
            ->normalize(
                $product,
                'flat',
                [
                    'scopeCode'   => 'mobile',
                    'localeCodes' => '',
                    'locale'      => 'en_US',
                    'filter_types' => [
                        'pim.transform.product_value.flat',
                        'pim.transform.product_value.flat.quick_export'
                    ],
                    'selected_properties' => null,
                ]
            )
            ->willReturn(['10.50', '10.00 GRAM', '10.00 EUR', '10/25/15']);

        $channelRepository->findOneByIdentifier('mobile')->willReturn($channel);

        $this->process($product)->shouldReturn(
            [
                'media'   => [],
                'product' => ['10.50', '10.00 GRAM', '10.00 EUR', '10/25/15']
            ]
        );
    }

    function it_returns_flat_data_with_french_attribute(
        $channelRepository,
        $serializer,
        $userProvider,
        $stepExecution,
        JobExecution $jobExecution,
        UserInterface $user,
        ChannelInterface $channel,
        ProductInterface $product,
        ProductValueInterface $number,
        AttributeInterface $attribute,
        MetricInterface $metric,
        ProductValueInterface $metricValue,
        ProductPriceInterface $price,
        ProductValueInterface $priceValue,
        JobParameters $jobParameters
    ) {
        $configuration = [
            'filters'     => [],
            'mainContext' => [
                'scope'     => 'mobile',
                'ui_locale' => 'fr_FR',
            ],
            'selected_properties'     => null,
        ];

        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('filters')->willReturn($configuration['filters']);
        $jobParameters->get('mainContext')->willReturn($configuration['mainContext']);
        $jobParameters->get('selected_properties')->willReturn($configuration['selected_properties']);

        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $jobExecution->getUser()->willReturn('michel');
        $userProvider->loadUserByUsername('michel')->willReturn($user);
        $user->getRoles()->willReturn(['ROLE_MICHEL']);

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
            ->normalize(
                $product,
                'flat',
                [
                    'scopeCode'   => 'mobile',
                    'localeCodes' => '',
                    'locale'      => 'fr_FR',
                    'filter_types' => [
                        'pim.transform.product_value.flat',
                        'pim.transform.product_value.flat.quick_export'
                    ],
                    'selected_properties' => null,
                ]
            )
            ->willReturn(['10,50', '10,00 GRAM', '10,00 EUR', '25/10/2015']);

        $channelRepository->findOneByIdentifier('mobile')->willReturn($channel);

        $this->process($product)->shouldReturn(
            [
                'media'   => [],
                'product' => ['10,50', '10,00 GRAM', '10,00 EUR', '25/10/2015']
            ]
        );
    }
}
