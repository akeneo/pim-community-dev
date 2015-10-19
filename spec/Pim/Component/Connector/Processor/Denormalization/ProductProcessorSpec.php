<?php

namespace spec\Pim\Component\Connector\Processor\Denormalization;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Builder\ProductBuilderInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Component\Catalog\Comparator\Filter\ProductFilterInterface;
use Pim\Component\Connector\ArrayConverter\StandardArrayConverterInterface;
use Pim\Component\Localization\Exception\FormatLocalizerException;
use Pim\Component\Localization\Localizer\LocalizedAttributeConverterInterface;
use Pim\Component\Localization\Localizer\ConverterInterface;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProductProcessorSpec extends ObjectBehavior
{
    function let(
        StandardArrayConverterInterface $arrayConverter,
        IdentifiableObjectRepositoryInterface $productRepository,
        ProductBuilderInterface $productBuilder,
        ObjectUpdaterInterface $productUpdater,
        ValidatorInterface $productValidator,
        StepExecution $stepExecution,
        ObjectDetacherInterface $productDetacher,
        ProductFilterInterface $productFilter,
        LocalizedAttributeConverterInterface $localizedConverter
    ) {
        $this->beConstructedWith(
            $arrayConverter,
            $productRepository,
            $productBuilder,
            $productUpdater,
            $productValidator,
            $productDetacher,
            $productFilter,
            $localizedConverter
        );
        $this->setStepExecution($stepExecution);
    }

    function it_is_a_configurable_step_execution_aware_processor()
    {
        $this->shouldBeAnInstanceOf('Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement');
        $this->shouldImplement('Akeneo\Bundle\BatchBundle\Item\ItemProcessorInterface');
        $this->shouldImplement('Akeneo\Bundle\BatchBundle\Step\StepExecutionAwareInterface');
    }

    function it_has_extra_configuration()
    {
        $this->getConfigurationFields()->shouldHaveCount(7);
    }

    function it_updates_an_existing_product(
        $arrayConverter,
        $productRepository,
        $productUpdater,
        $productValidator,
        $productFilter,
        $localizedConverter,
        ProductInterface $product,
        ConstraintViolationListInterface $violationList
    ) {
        $productRepository->getIdentifierProperties()->willReturn(['sku']);
        $productRepository->findOneByIdentifier(Argument::any())->willReturn($product);
        $product->getId()->willReturn(42);

        $originalData = [
            'sku' => 'tshirt',
            'family' => 'TShirt',
            'description-en_US-mobile' => 'My description',
            'name-fr_FR' => 'T-shirt super beau',
            'name-en_US' => 'My awesome T-shirt'
        ];
        $convertedData =                 [
            'sku' => [
                [
                    'locale' => null,
                    'scope' =>  null,
                    'data' => 'tshirt'
                ],
            ],
            'family' => 'Summer Tshirt',
            'name' => [
                [
                    'locale' => 'fr_FR',
                    'scope' =>  null,
                    'data' => 'Mon super beau t-shirt'
                ],
                [
                    'locale' => 'en_US',
                    'scope' =>  null,
                    'data' => 'My very awesome T-shirt'
                ]
            ],
            'description' => [
                [
                    'locale' => 'en_US',
                    'scope' =>  'mobile',
                    'data' => 'My awesome description'
                ]
            ]
        ];
        $converterOptions = [
            'mapping'           => ['family' => 'family', 'categories' => 'categories', 'groups' => 'groups'],
            'default_values'    => ['enabled' => true],
            'with_associations' => false,
        ];
        $arrayConverter
            ->convert($originalData, $converterOptions)
            ->willReturn($convertedData);

        $filteredData = [
            'family' => 'Summer Tshirt',
            'name' => [
                [
                    'locale' => 'fr_FR',
                    'scope' =>  null,
                    'data' => 'Mon super beau t-shirt'
                ],
                [
                    'locale' => 'en_US',
                    'scope' =>  null,
                    'data' => 'My very awesome T-shirt'
                ]
            ],
            'description' => [
                [
                    'locale' => 'en_US',
                    'scope' =>  'mobile',
                    'data' => 'My awesome description'
                ]
            ]
        ];

        $localizedConverter->convert($convertedData, [
            'decimal_separator' => '.',
            'date_format'       => 'Y-m-d'
        ])->willReturn($convertedData);
        $productFilter->filter($product, $filteredData)->willReturn($filteredData);

        $productUpdater
            ->update($product, $filteredData)
            ->shouldBeCalled();

        $productValidator
            ->validate($product)
            ->willReturn($violationList);

        $this
            ->process($originalData)
            ->shouldReturn($product);
    }

    function it_updates_an_existing_product_with_filtered_values(
        $arrayConverter,
        $productRepository,
        $productUpdater,
        $productValidator,
        $productFilter,
        $localizedConverter,
        ProductInterface $product,
        ConstraintViolationListInterface $violationList
    ) {
        $productRepository->getIdentifierProperties()->willReturn(['sku']);
        $productRepository->findOneByIdentifier(Argument::any())->willReturn($product);
        $product->getId()->willReturn(42);

        $originalData = [
            'sku' => 'tshirt',
            'family' => 'TShirt',
            'description-en_US-mobile' => 'My description',
            'name-fr_FR' => 'T-shirt super beau',
            'name-en_US' => 'My awesome T-shirt'
        ];
        $convertedData =                 [
            'sku' => [
                [
                    'locale' => null,
                    'scope' =>  null,
                    'data' => 'tshirt'
                ],
            ],
            'family' => 'Tshirt',
            'name' => [
                [
                    'locale' => 'fr_FR',
                    'scope' =>  null,
                    'data' => 'Mon super beau t-shirt'
                ],
                [
                    'locale' => 'en_US',
                    'scope' =>  null,
                    'data' => 'My awesome T-shirt'
                ]
            ],
            'description' => [
                [
                    'locale' => 'en_US',
                    'scope' =>  'mobile',
                    'data' => 'My awesome description'
                ]
            ]
        ];
        $converterOptions = [
            'mapping'           => ['family' => 'family', 'categories' => 'categories', 'groups' => 'groups'],
            'default_values'    => ['enabled' => true],
            'with_associations' => false
        ];
        $arrayConverter
            ->convert($originalData, $converterOptions)
            ->willReturn($convertedData);

        $localizedConverter->convert($convertedData, [
            'decimal_separator' => '.',
            'date_format'       => 'Y-m-d'
        ])->willReturn($convertedData);

        $preFilteredData = $filteredData = [
            'family' => 'Tshirt',
            'name' => [
                [
                    'locale' => 'fr_FR',
                    'scope' =>  null,
                    'data' => 'Mon super beau t-shirt'
                ],
                [
                    'locale' => 'en_US',
                    'scope' =>  null,
                    'data' => 'My awesome T-shirt'
                ]
            ],
            'description' => [
                [
                    'locale' => 'en_US',
                    'scope' =>  'mobile',
                    'data' => 'My awesome description'
                ]
            ]
        ];

        unset($filteredData['family'], $filteredData['name'][1]);
        $productFilter->filter($product, $preFilteredData)->willReturn($filteredData);

        $productUpdater
            ->update($product, $filteredData)
            ->shouldBeCalled();

        $productValidator
            ->validate($product)
            ->willReturn($violationList);

        $this
            ->process($originalData)
            ->shouldReturn($product);
    }

    function it_updates_an_existing_product_without_filtered_values(
        $arrayConverter,
        $productRepository,
        $productUpdater,
        $productValidator,
        $productFilter,
        $localizedConverter,
        ProductInterface $product,
        ConstraintViolationListInterface $violationList
    ) {
        $productRepository->getIdentifierProperties()->willReturn(['sku']);
        $productRepository->findOneByIdentifier(Argument::any())->willReturn($product);
        $product->getId()->willReturn(42);

        $originalData = [
            'sku' => 'tshirt',
            'family' => 'TShirt',
            'description-en_US-mobile' => 'My description',
            'name-fr_FR' => 'T-shirt super beau',
            'name-en_US' => 'My awesome T-shirt'
        ];
        $convertedData =                 [
            'sku' => [
                [
                    'locale' => null,
                    'scope' =>  null,
                    'data' => 'tshirt'
                ],
            ],
            'family' => 'Tshirt',
            'name' => [
                [
                    'locale' => 'fr_FR',
                    'scope' =>  null,
                    'data' => 'Mon super beau t-shirt'
                ],
                [
                    'locale' => 'en_US',
                    'scope' =>  null,
                    'data' => 'My awesome T-shirt'
                ]
            ],
            'description' => [
                [
                    'locale' => 'en_US',
                    'scope' =>  'mobile',
                    'data' => 'My awesome description'
                ]
            ]
        ];
        $converterOptions = [
            'mapping'           => ['family' => 'family', 'categories' => 'categories', 'groups' => 'groups'],
            'default_values'    => ['enabled' => true],
            'with_associations' => false
        ];
        $arrayConverter
            ->convert($originalData, $converterOptions)
            ->willReturn($convertedData);

        $localizedConverter->convert($convertedData, [
            'decimal_separator' => '.',
            'date_format'       => 'Y-m-d'
        ])->willReturn($convertedData);

        $filteredData = [
            'family' => 'Tshirt',
            'name' => [
                [
                    'locale' => 'fr_FR',
                    'scope' =>  null,
                    'data' => 'Mon super beau t-shirt'
                ],
                [
                    'locale' => 'en_US',
                    'scope' =>  null,
                    'data' => 'My awesome T-shirt'
                ]
            ],
            'description' => [
                [
                    'locale' => 'en_US',
                    'scope' =>  'mobile',
                    'data' => 'My awesome description'
                ]
            ]
        ];

        $productFilter->filter($product, [])->shouldNotBeCalled();

        $productUpdater
            ->update($product, $filteredData)
            ->shouldBeCalled();

        $productValidator
            ->validate($product)
            ->willReturn($violationList);

        $this->setEnabledComparison(false);
        $this
            ->process($originalData)
            ->shouldReturn($product);
    }

    function it_creates_a_product(
        $arrayConverter,
        $productRepository,
        $productBuilder,
        $productUpdater,
        $productValidator,
        $productFilter,
        $localizedConverter,
        ProductInterface $product,
        ConstraintViolationListInterface $violationList
    ) {
        $productRepository->getIdentifierProperties()->willReturn(['sku']);
        $productRepository->findOneByIdentifier('tshirt')->willReturn(false);

        $productBuilder->createProduct('tshirt', 'Tshirt')->willReturn($product);

        $originalData = [
            'sku' => 'tshirt',
            'family' => 'TShirt',
            'description-en_US-mobile' => 'My description',
            'name-fr_FR' => 'T-shirt super beau',
            'name-en_US' => 'My awesome T-shirt'
        ];
        $convertedData =                 [
            'sku' => [
                [
                    'locale' => null,
                    'scope' =>  null,
                    'data' => 'tshirt'
                ],
            ],
            'family' => 'Tshirt',
            'name' => [
                [
                    'locale' => 'fr_FR',
                    'scope' =>  null,
                    'data' => 'T-shirt super beau'
                ],
                [
                    'locale' => 'en_US',
                    'scope' =>  null,
                    'data' => 'My awesome T-shirt'
                ]
            ],
            'description' => [
                [
                    'locale' => 'en_US',
                    'scope' =>  'mobile',
                    'data' => 'My description'
                ]
            ]
        ];
        $converterOptions = [
            'mapping' => ['family' => 'family', 'categories' => 'categories', 'groups' => 'groups'],
            'default_values' => ['enabled' => true],
            'with_associations' => false
        ];
        $arrayConverter
            ->convert($originalData, $converterOptions)
            ->willReturn($convertedData);

        $localizedConverter->convert($convertedData, [
            'decimal_separator' => '.',
            'date_format'       => 'Y-m-d'
        ])->willReturn($convertedData);

        $filteredData = [
            'family' => 'Tshirt',
            'name' => [
                [
                    'locale' => 'fr_FR',
                    'scope' =>  null,
                    'data' => 'T-shirt super beau'
                ],
                [
                    'locale' => 'en_US',
                    'scope' =>  null,
                    'data' => 'My awesome T-shirt'
                ]
            ],
            'description' => [
                [
                    'locale' => 'en_US',
                    'scope' =>  'mobile',
                    'data' => 'My description'
                ]
            ]
        ];

        $productFilter->filter($product, $filteredData)->willReturn($filteredData);

        $productUpdater
            ->update($product, $filteredData)
            ->shouldBeCalled();

        $productValidator
            ->validate($product)
            ->willReturn($violationList);

        $this
            ->process($originalData)
            ->shouldReturn($product);
    }

    function it_skips_a_product_when_identifier_is_empty($arrayConverter, $productRepository, $localizedConverter)
    {
        $productRepository->getIdentifierProperties()->willReturn(['sku']);

        $originalData = [
            'sku' => '',
            'family' => 'TShirt'
        ];
        $convertedData =                 [
            'sku' => [
                [
                    'locale' => null,
                    'scope' =>  null,
                    'data' => null
                ],
            ],
            'family' => 'Tshirt',
        ];

        $converterOptions = [
            'mapping' => ['family' => 'family', 'categories' => 'categories', 'groups' => 'groups'],
            'default_values' => ['enabled' => true],
            'with_associations' => false
        ];
        $arrayConverter
            ->convert($originalData, $converterOptions)
            ->willReturn($convertedData);

        $localizedConverter->convert($convertedData, [
            'decimal_separator' => '.',
            'date_format'       => 'Y-m-d'
        ])->willReturn($convertedData);

        $this
            ->shouldThrow('Akeneo\Bundle\BatchBundle\Item\InvalidItemException')
            ->during(
                'process',
                [$originalData]
            );
    }

    function it_skips_a_product_when_update_fails(
        $arrayConverter,
        $productRepository,
        $productBuilder,
        $productUpdater,
        $productDetacher,
        $productFilter,
        $localizedConverter,
        ProductInterface $product
    ) {
        $productRepository->getIdentifierProperties()->willReturn(['sku']);
        $productRepository->findOneByIdentifier('tshirt')->willReturn(false);

        $productBuilder->createProduct('tshirt', 'Tshirt')->willReturn($product);

        $originalData = [
            'sku' => 'tshirt',
            'family' => 'TShirt',
            'description-en_US-mobile' => 'My description',
            'name-fr_FR' => 'T-shirt super beau',
            'name-en_US' => 'My awesome T-shirt'
        ];
        $convertedData =                 [
            'sku' => [
                [
                    'locale' => null,
                    'scope' =>  null,
                    'data' => 'tshirt'
                ],
            ],
            'family' => 'Tshirt',
            'name' => [
                [
                    'locale' => 'fr_FR',
                    'scope' =>  null,
                    'data' => 'T-shirt super beau'
                ],
                [
                    'locale' => 'en_US',
                    'scope' =>  null,
                    'data' => 'My awesome T-shirt'
                ]
            ],
            'description' => [
                [
                    'locale' => 'en_US',
                    'scope' =>  'mobile',
                    'data' => 'My description'
                ]
            ]
        ];
        $converterOptions = [
            'mapping' => ['family' => 'family', 'categories' => 'categories', 'groups' => 'groups'],
            'default_values' => ['enabled' => true],
            'with_associations' => false
        ];
        $arrayConverter
            ->convert($originalData, $converterOptions)
            ->willReturn($convertedData);

        $localizedConverter->convert($convertedData, [
            'decimal_separator' => '.',
            'date_format'       => 'Y-m-d'
        ])->willReturn($convertedData);

        $filteredData = [
            'family' => 'Tshirt',
            'name' => [
                [
                    'locale' => 'fr_FR',
                    'scope' =>  null,
                    'data' => 'T-shirt super beau'
                ],
                [
                    'locale' => 'en_US',
                    'scope' =>  null,
                    'data' => 'My awesome T-shirt'
                ]
            ],
            'description' => [
                [
                    'locale' => 'en_US',
                    'scope' =>  'mobile',
                    'data' => 'My description'
                ]
            ]
        ];

        $productFilter->filter($product, $filteredData)->willReturn($filteredData);

        $productUpdater
            ->update($product, $filteredData)
            ->willThrow(new \InvalidArgumentException('family does not exists'));

        $productDetacher->detach($product)->shouldBeCalled();

        $this
            ->shouldThrow('Akeneo\Bundle\BatchBundle\Item\InvalidItemException')
            ->during(
                'process',
                [$originalData]
            );
    }

    function it_skips_a_product_when_object_is_invalid(
        $arrayConverter,
        $productRepository,
        $productBuilder,
        $productUpdater,
        $productValidator,
        $productDetacher,
        $productFilter,
        $localizedConverter,
        ProductInterface $product
    ) {
        $productRepository->getIdentifierProperties()->willReturn(['sku']);
        $productRepository->findOneByIdentifier('tshirt')->willReturn(false);

        $productBuilder->createProduct('tshirt', 'Tshirt')->willReturn($product);

        $originalData = [
            'sku' => 'tshirt',
            'family' => 'TShirt',
            'description-en_US-mobile' => 'My description',
            'name-fr_FR' => 'T-shirt super beau',
            'name-en_US' => 'My awesome T-shirt'
        ];
        $convertedData =                 [
            'sku' => [
                [
                    'locale' => null,
                    'scope' =>  null,
                    'data' => 'tshirt'
                ],
            ],
            'family' => 'Tshirt',
            'name' => [
                [
                    'locale' => 'fr_FR',
                    'scope' =>  null,
                    'data' => 'T-shirt super beau'
                ],
                [
                    'locale' => 'en_US',
                    'scope' =>  null,
                    'data' => 'My awesome T-shirt'
                ]
            ],
            'description' => [
                [
                    'locale' => 'en_US',
                    'scope' =>  'mobile',
                    'data' => 'My description'
                ]
            ]
        ];
        $converterOptions = [
            'mapping'           => ['family' => 'family', 'categories' => 'categories', 'groups' => 'groups'],
            'default_values'    => ['enabled' => true],
            'with_associations' => false
        ];
        $arrayConverter
            ->convert($originalData, $converterOptions)
            ->willReturn($convertedData);

        $localizedConverter->convert($convertedData, [
            'decimal_separator' => '.',
            'date_format'       => 'Y-m-d'
        ])->willReturn($convertedData);

        $filteredData = [
            'family' => 'Tshirt',
            'name' => [
                [
                    'locale' => 'fr_FR',
                    'scope' =>  null,
                    'data' => 'T-shirt super beau'
                ],
                [
                    'locale' => 'en_US',
                    'scope' =>  null,
                    'data' => 'My awesome T-shirt'
                ]
            ],
            'description' => [
                [
                    'locale' => 'en_US',
                    'scope' =>  'mobile',
                    'data' => 'My description'
                ]
            ]
        ];

        $productFilter->filter($product, $filteredData)->willReturn($filteredData);

        $productUpdater
            ->update($product, $filteredData)
            ->shouldBeCalled();

        $violation = new ConstraintViolation('There is a small problem with option code', 'foo', [], 'bar', 'code', 'mycode');
        $violations = new ConstraintViolationList([$violation]);
        $productValidator
            ->validate($product)
            ->willReturn($violations);

        $productDetacher->detach($product);

        $this
            ->shouldThrow('Akeneo\Bundle\BatchBundle\Item\InvalidItemException')
            ->during(
                'process',
                [$originalData]
            );
    }

    function it_skips_a_product_when_there_is_nothing_to_update(
        $arrayConverter,
        $productRepository,
        $productBuilder,
        $productUpdater,
        $productFilter,
        $localizedConverter,
        ProductInterface $product
    ) {
        $productRepository->getIdentifierProperties()->willReturn(['sku']);
        $productRepository->findOneByIdentifier('tshirt')->willReturn(false);

        $productBuilder->createProduct('tshirt', 'Tshirt')->willReturn($product);

        $originalData = [
            'sku' => 'tshirt',
            'family' => 'TShirt',
            'description-en_US-mobile' => 'My description',
            'name-fr_FR' => 'T-shirt super beau',
            'name-en_US' => 'My awesome T-shirt'
        ];
        $convertedData =                 [
            'sku' => [
                [
                    'locale' => null,
                    'scope' =>  null,
                    'data' => 'tshirt'
                ],
            ],
            'family' => 'Tshirt',
            'name' => [
                [
                    'locale' => 'fr_FR',
                    'scope' =>  null,
                    'data' => 'T-shirt super beau'
                ],
                [
                    'locale' => 'en_US',
                    'scope' =>  null,
                    'data' => 'My awesome T-shirt'
                ]
            ],
            'description' => [
                [
                    'locale' => 'en_US',
                    'scope' =>  'mobile',
                    'data' => 'My description'
                ]
            ]
        ];
        $converterOptions = [
            'mapping' => ['family' => 'family', 'categories' => 'categories', 'groups' => 'groups'],
            'default_values' => ['enabled' => true],
            'with_associations' => false
        ];
        $arrayConverter
            ->convert($originalData, $converterOptions)
            ->willReturn($convertedData);

        $localizedConverter->convert($convertedData, [
            'decimal_separator' => '.',
            'date_format'       => 'Y-m-d'
        ])->willReturn($convertedData);

        $filteredData = [
            'family' => 'Tshirt',
            'name' => [
                [
                    'locale' => 'fr_FR',
                    'scope' =>  null,
                    'data' => 'T-shirt super beau'
                ],
                [
                    'locale' => 'en_US',
                    'scope' =>  null,
                    'data' => 'My awesome T-shirt'
                ]
            ],
            'description' => [
                [
                    'locale' => 'en_US',
                    'scope' =>  'mobile',
                    'data' => 'My description'
                ]
            ]
        ];

        $productFilter->filter($product, $filteredData)->willReturn([]);

        $productUpdater
            ->update($product, $filteredData)->shouldNotBeCalled();

        $this
            ->process($originalData)
            ->shouldReturn(null);
    }

    function it_updates_an_existing_product_with_localized_value(
        $arrayConverter,
        $productRepository,
        $productUpdater,
        $productValidator,
        $productFilter,
        $localizedConverter,
        ProductInterface $product,
        ConstraintViolationListInterface $violationList
    ) {
        $productRepository->getIdentifierProperties()->willReturn(['sku']);
        $productRepository->findOneByIdentifier(Argument::any())->willReturn($product);
        $product->getId()->willReturn(42);
        $this->setDecimalSeparator(',');
        $this->setDateFormat('d/m/Y');

        $originalData = [
            'sku'    => 'tshirt',
            'number' => '10.00',
            'date'   => null
        ];
        $postConverterData = $convertedData = [
            'sku' => [
                [
                    'locale' => null,
                    'scope'  =>  null,
                    'data'   => 'tshirt'
                ],
            ],
            'number' => [
                [
                    'locale' => null,
                    'scope'  =>  null,
                    'data'   => '10,45'
                ]
            ],
            'date' => [
                [
                    'locale' => null,
                    'scope'  =>  null,
                    'data'   => '20/10/2015'
                ]
            ]
        ];
        $converterOptions = [
            'mapping'           => ['family' => 'family', 'categories' => 'categories', 'groups' => 'groups'],
            'default_values'    => ['enabled' => true],
            'with_associations' => false,
        ];
        $arrayConverter
            ->convert($originalData, $converterOptions)
            ->willReturn($convertedData);

        $filteredData = [
            'number' => [
                [
                    'locale' => null,
                    'scope' =>  null,
                    'data' => '10.45'
                ]
            ],
            'date' => [
                [
                    'locale' => null,
                    'scope'  =>  null,
                    'data'   => '2015-10-20'
                ]
            ]
        ];

        $postConverterData['number'][0]['data'] = '10.45';
        $postConverterData['date'][0]['data'] = '2015-10-20';
        $localizedConverter->convert($convertedData, [
            'decimal_separator' => ',',
            'date_format'       => 'd/m/Y'
        ])->willReturn($postConverterData);
        $productFilter->filter($product, $filteredData)->willReturn($filteredData);

        $productUpdater
            ->update($product, $filteredData)
            ->shouldBeCalled();

        $productValidator
            ->validate($product)
            ->willReturn($violationList);

        $this
            ->process($originalData)
            ->shouldReturn($product);
    }

    function it_skips_a_product_if_format_of_localized_attribute_is_not_expected(
        $arrayConverter,
        $localizedConverter,
        $productRepository,
        ProductInterface $product
    ) {
        $productRepository->getIdentifierProperties()->willReturn(['sku']);
        $productRepository->findOneByIdentifier(Argument::any())->willReturn($product);
        $product->getId()->willReturn(42);

        $originalData = [
            'sku'    => 'tshirt',
            'number' => '10.00'
        ];
        $convertedData = [
            'sku' => [
                [
                    'locale' => null,
                    'scope'  =>  null,
                    'data'   => 'tshirt'
                ],
            ],
            'number' => [
                [
                    'locale' => null,
                    'scope'  =>  null,
                    'data'   => '10,45'
                ]
            ]
        ];
        $converterOptions = [
            'mapping'           => ['family' => 'family', 'categories' => 'categories', 'groups' => 'groups'],
            'default_values'    => ['enabled' => true],
            'with_associations' => false,
        ];
        $arrayConverter
            ->convert($originalData, $converterOptions)
            ->willReturn($convertedData);

        $localizedConverter->convert($convertedData, [
            'decimal_separator' => '.',
            'date_format'       => 'Y-m-d'
        ])->willThrow(new FormatLocalizerException('number', '.'));

        $this
            ->shouldThrow('Akeneo\Bundle\BatchBundle\Item\InvalidItemException')
            ->during(
                'process',
                [$originalData]
            );
    }

    function it_skips_a_product_when_there_is_nothing_to_update_with_localized_value(
        $arrayConverter,
        $productRepository,
        $productBuilder,
        $productUpdater,
        $productFilter,
        $localizedConverter,
        ProductInterface $product
    ) {
        $productRepository->getIdentifierProperties()->willReturn(['sku']);
        $productRepository->findOneByIdentifier('tshirt')->willReturn(false);
        $this->setDecimalSeparator(',');

        $productBuilder->createProduct('tshirt', null)->willReturn($product);

        $originalData = [
            'sku' => 'tshirt',
            'number' => '10.45',
        ];
        $postConvertedData = $convertedData = [
            'sku' => [
                [
                    'locale' => null,
                    'scope' =>  null,
                    'data' => 'tshirt'
                ],
            ],
            'number' => [
                [
                    'locale' => null,
                    'scope' =>  null,
                    'data' => '10,45'
                ],
            ]
        ];

        $converterOptions = [
            'mapping' => ['family' => 'family', 'categories' => 'categories', 'groups' => 'groups'],
            'default_values' => ['enabled' => true],
            'with_associations' => false
        ];
        $arrayConverter
            ->convert($originalData, $converterOptions)
            ->willReturn($convertedData);

        $postConvertedData['number'][0]['data'] = '10.45';
        $localizedConverter->convert($convertedData, [
            'decimal_separator' => ',',
            'date_format'       => 'Y-m-d'
        ])->willReturn($postConvertedData);

        $filteredData = [
            'number' => [
                [
                    'locale' => null,
                    'scope' =>  null,
                    'data' => '10.45'
                ],
            ]
        ];

        $productFilter->filter($product, $filteredData)->willReturn([]);

        $productUpdater
            ->update($product, $filteredData)->shouldNotBeCalled();

        $this
            ->process($originalData)
            ->shouldReturn(null);
    }

    function it_updates_an_existing_product_and_does_not_change_his_state(
        $arrayConverter,
        $productRepository,
        $productUpdater,
        $productValidator,
        $productFilter,
        $localizedConverter,
        ProductInterface $product,
        ConstraintViolationListInterface $violationList
    ) {
        $productRepository->getIdentifierProperties()->willReturn(['sku']);
        $productRepository->findOneByIdentifier(Argument::any())->willReturn($product);
        $product->getId()->willReturn(42);

        $originalData = [
            'sku' => 'tshirt',
            'family' => 'TShirt',
            'description-en_US-mobile' => 'My description',
            'name-fr_FR' => 'T-shirt super beau',
            'name-en_US' => 'My awesome T-shirt',
        ];
        $convertedData = [
            'sku' => [
                [
                    'locale' => null,
                    'scope' =>  null,
                    'data' => 'tshirt'
                ],
            ],
            'family' => 'Summer Tshirt',
            'name' => [
                [
                    'locale' => 'fr_FR',
                    'scope' =>  null,
                    'data' => 'Mon super beau t-shirt'
                ],
                [
                    'locale' => 'en_US',
                    'scope' =>  null,
                    'data' => 'My very awesome T-shirt'
                ]
            ],
            'description' => [
                [
                    'locale' => 'en_US',
                    'scope' =>  'mobile',
                    'data' => 'My awesome description'
                ]
            ],
            'enabled' => false,
        ];
        $converterOptions = [
            'mapping'           => ['family' => 'family', 'categories' => 'categories', 'groups' => 'groups'],
            'default_values'    => ['enabled' => true],
            'with_associations' => false,
        ];
        $arrayConverter
            ->convert($originalData, $converterOptions)
            ->willReturn($convertedData);

        $filteredData = [
            'family' => 'Summer Tshirt',
            'name' => [
                [
                    'locale' => 'fr_FR',
                    'scope' =>  null,
                    'data' => 'Mon super beau t-shirt'
                ],
                [
                    'locale' => 'en_US',
                    'scope' =>  null,
                    'data' => 'My very awesome T-shirt'
                ]
            ],
            'description' => [
                [
                    'locale' => 'en_US',
                    'scope' =>  'mobile',
                    'data' => 'My awesome description'
                ]
            ],
        ];

        $localizedConverter->convert($convertedData, [
            'decimal_separator' => '.',
            'date_format'       => 'Y-m-d'
        ])->willReturn($convertedData);
        $productFilter->filter($product, $filteredData)->willReturn($filteredData);

        $productUpdater
            ->update($product, $filteredData)
            ->shouldBeCalled();

        $productValidator
            ->validate($product)
            ->willReturn($violationList);

        $this
            ->process($originalData)
            ->shouldReturn($product);
    }
}
