<?php

namespace spec\Pim\Component\Connector\Processor\Denormalization;

use Akeneo\Component\Batch\Job\JobParameters;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Builder\ProductBuilderInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Comparator\Filter\ProductFilterInterface;
use Pim\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Pim\Component\Catalog\Localization\Localizer\AttributeConverterInterface;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProductProcessorSpec extends ObjectBehavior
{
    function let(
        ArrayConverterInterface $arrayConverter,
        IdentifiableObjectRepositoryInterface $productRepository,
        ProductBuilderInterface $productBuilder,
        ObjectUpdaterInterface $productUpdater,
        ValidatorInterface $productValidator,
        StepExecution $stepExecution,
        ObjectDetacherInterface $productDetacher,
        ProductFilterInterface $productFilter,
        AttributeConverterInterface $localizedConverter
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
        $this->shouldBeAnInstanceOf('Akeneo\Component\Batch\Item\AbstractConfigurableStepElement');
        $this->shouldImplement('Akeneo\Component\Batch\Item\ItemProcessorInterface');
        $this->shouldImplement('Akeneo\Component\Batch\Step\StepExecutionAwareInterface');
    }

    function it_updates_an_existing_product(
        $arrayConverter,
        $productRepository,
        $productUpdater,
        $productValidator,
        $productFilter,
        $localizedConverter,
        $stepExecution,
        ProductInterface $product,
        ConstraintViolationListInterface $violationList,
        JobParameters $jobParameters
    ) {
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('enabledComparison')->willReturn(true);
        $jobParameters->get('familyColumn')->willReturn('family');
        $jobParameters->get('categoriesColumn')->willReturn('categories');
        $jobParameters->get('groupsColumn')->willReturn('groups');
        $jobParameters->get('enabled')->willReturn(true);
        $jobParameters->get('decimalSeparator')->willReturn('.');
        $jobParameters->get('dateFormat')->willReturn('yyyy-MM-dd');

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

        $localizedConverter->convertToDefaultFormats($convertedData, [
            'decimal_separator' => '.',
            'date_format'       => 'yyyy-MM-dd'
        ])->willReturn($convertedData);
        $localizedConverter->getViolations()->willReturn($violationList);
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
        $stepExecution,
        ProductInterface $product,
        ConstraintViolationListInterface $violationList,
        JobParameters $jobParameters
    ) {
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('enabledComparison')->willReturn(true);
        $jobParameters->get('familyColumn')->willReturn('family');
        $jobParameters->get('categoriesColumn')->willReturn('categories');
        $jobParameters->get('groupsColumn')->willReturn('groups');
        $jobParameters->get('enabled')->willReturn(true);
        $jobParameters->get('decimalSeparator')->willReturn('.');
        $jobParameters->get('dateFormat')->willReturn('yyyy-MM-dd');

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

        $localizedConverter->convertToDefaultFormats($convertedData, [
            'decimal_separator' => '.',
            'date_format'       => 'yyyy-MM-dd'
        ])->willReturn($convertedData);
        $localizedConverter->getViolations()->willReturn($violationList);

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
        $stepExecution,
        ProductInterface $product,
        ConstraintViolationListInterface $violationList,
        JobParameters $jobParameters
    ) {
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('enabledComparison')->willReturn(false);
        $jobParameters->get('familyColumn')->willReturn('family');
        $jobParameters->get('categoriesColumn')->willReturn('categories');
        $jobParameters->get('groupsColumn')->willReturn('groups');
        $jobParameters->get('enabled')->willReturn(true);
        $jobParameters->get('decimalSeparator')->willReturn('.');
        $jobParameters->get('dateFormat')->willReturn('yyyy-MM-dd');

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

        $localizedConverter->convertToDefaultFormats($convertedData, [
            'decimal_separator' => '.',
            'date_format'       => 'yyyy-MM-dd'
        ])->willReturn($convertedData);
        $localizedConverter->getViolations()->willReturn($violationList);

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
        $stepExecution,
        ProductInterface $product,
        ConstraintViolationListInterface $violationList,
        JobParameters $jobParameters
    ) {
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('enabledComparison')->willReturn(true);
        $jobParameters->get('familyColumn')->willReturn('family');
        $jobParameters->get('categoriesColumn')->willReturn('categories');
        $jobParameters->get('groupsColumn')->willReturn('groups');
        $jobParameters->get('enabled')->willReturn(true);
        $jobParameters->get('decimalSeparator')->willReturn('.');
        $jobParameters->get('dateFormat')->willReturn('yyyy-MM-dd');

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

        $localizedConverter->convertToDefaultFormats($convertedData, [
            'decimal_separator' => '.',
            'date_format'       => 'yyyy-MM-dd'
        ])->willReturn($convertedData);
        $localizedConverter->getViolations()->willReturn($violationList);

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

    function it_skips_a_product_when_identifier_is_empty(
        $arrayConverter,
        $productRepository,
        $localizedConverter,
        $stepExecution,
        ConstraintViolationListInterface $violationList,
        JobParameters $jobParameters
    ) {
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('enabledComparison')->willReturn(true);
        $jobParameters->get('familyColumn')->willReturn('family');
        $jobParameters->get('categoriesColumn')->willReturn('categories');
        $jobParameters->get('groupsColumn')->willReturn('groups');
        $jobParameters->get('enabled')->willReturn(true);
        $jobParameters->get('decimalSeparator')->willReturn('.');
        $jobParameters->get('dateFormat')->willReturn('yyyy-MM-dd');

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

        $localizedConverter->convertToDefaultFormats($convertedData, [
            'decimal_separator' => '.',
            'date_format'       => 'yyyy-MM-dd'
        ])->willReturn($convertedData);
        $localizedConverter->getViolations()->willReturn($violationList);

        $stepExecution->incrementSummaryInfo('skip')->shouldBeCalled();

        $this
            ->shouldThrow('Akeneo\Component\Batch\Item\InvalidItemException')
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
        $stepExecution,
        ProductInterface $product,
        ConstraintViolationListInterface $violationList,
        JobParameters $jobParameters
    ) {
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('enabledComparison')->willReturn(true);
        $jobParameters->get('familyColumn')->willReturn('family');
        $jobParameters->get('categoriesColumn')->willReturn('categories');
        $jobParameters->get('groupsColumn')->willReturn('groups');
        $jobParameters->get('enabled')->willReturn(true);
        $jobParameters->get('decimalSeparator')->willReturn('.');
        $jobParameters->get('dateFormat')->willReturn('yyyy-MM-dd');

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

        $localizedConverter->convertToDefaultFormats($convertedData, [
            'decimal_separator' => '.',
            'date_format'       => 'yyyy-MM-dd'
        ])->willReturn($convertedData);
        $localizedConverter->getViolations()->willReturn($violationList);

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
        $stepExecution->incrementSummaryInfo('skip')->shouldBeCalled();

        $this
            ->shouldThrow('Akeneo\Component\Batch\Item\InvalidItemException')
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
        $stepExecution,
        ProductInterface $product,
        ConstraintViolationListInterface $violationList,
        JobParameters $jobParameters
    ) {
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('enabledComparison')->willReturn(true);
        $jobParameters->get('familyColumn')->willReturn('family');
        $jobParameters->get('categoriesColumn')->willReturn('categories');
        $jobParameters->get('groupsColumn')->willReturn('groups');
        $jobParameters->get('enabled')->willReturn(true);
        $jobParameters->get('decimalSeparator')->willReturn('.');
        $jobParameters->get('dateFormat')->willReturn('yyyy-MM-dd');

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

        $localizedConverter->convertToDefaultFormats($convertedData, [
            'decimal_separator' => '.',
            'date_format'       => 'yyyy-MM-dd'
        ])->willReturn($convertedData);
        $localizedConverter->getViolations()->willReturn($violationList);

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

        $productDetacher->detach($product)->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('skip')->shouldBeCalled();
        $this
            ->shouldThrow('Akeneo\Component\Batch\Item\InvalidItemException')
            ->during(
                'process',
                [$originalData]
            );
    }

    function it_skips_a_product_when_there_is_nothing_to_update(
        $arrayConverter,
        $productRepository,
        $productUpdater,
        $productDetacher,
        $productFilter,
        $localizedConverter,
        $stepExecution,
        ProductInterface $product,
        ConstraintViolationListInterface $violationList,
        JobParameters $jobParameters
    ) {
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('enabledComparison')->willReturn(true);
        $jobParameters->get('familyColumn')->willReturn('family');
        $jobParameters->get('categoriesColumn')->willReturn('categories');
        $jobParameters->get('groupsColumn')->willReturn('groups');
        $jobParameters->get('enabled')->willReturn(true);
        $jobParameters->get('decimalSeparator')->willReturn('.');
        $jobParameters->get('dateFormat')->willReturn('yyyy-MM-dd');

        $productRepository->getIdentifierProperties()->willReturn(['sku']);
        $productRepository->findOneByIdentifier('tshirt')->willReturn($product);
        $product->getId()->willReturn(1);

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

        $localizedConverter->convertToDefaultFormats($convertedData, [
            'decimal_separator' => '.',
            'date_format'       => 'yyyy-MM-dd'
        ])->willReturn($convertedData);
        $localizedConverter->getViolations()->willReturn($violationList);

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

        $productDetacher->detach($product)->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('product_skipped_no_diff')->shouldBeCalled();

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
        $stepExecution,
        ProductInterface $product,
        ConstraintViolationListInterface $violationList,
        JobParameters $jobParameters
    ) {
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('enabledComparison')->willReturn(true);
        $jobParameters->get('familyColumn')->willReturn('family');
        $jobParameters->get('categoriesColumn')->willReturn('categories');
        $jobParameters->get('groupsColumn')->willReturn('groups');
        $jobParameters->get('enabled')->willReturn(true);
        $jobParameters->get('decimalSeparator')->willReturn(',');
        $jobParameters->get('dateFormat')->willReturn('dd/MM/yyyy');

        $productRepository->getIdentifierProperties()->willReturn(['sku']);
        $productRepository->findOneByIdentifier(Argument::any())->willReturn($product);
        $product->getId()->willReturn(42);

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
        $localizedConverter->convertToDefaultFormats($convertedData, [
            'decimal_separator' => ',',
            'date_format'       => 'dd/MM/yyyy'
        ])->willReturn($postConverterData);
        $localizedConverter->getViolations()->willReturn($violationList);

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
        $stepExecution,
        ProductInterface $product,
        JobParameters $jobParameters
    ) {
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('enabledComparison')->willReturn(true);
        $jobParameters->get('familyColumn')->willReturn('family');
        $jobParameters->get('categoriesColumn')->willReturn('categories');
        $jobParameters->get('groupsColumn')->willReturn('groups');
        $jobParameters->get('enabled')->willReturn(true);
        $jobParameters->get('decimalSeparator')->willReturn('.');
        $jobParameters->get('dateFormat')->willReturn('yyyy-MM-dd');

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

        $data = $convertedData;
        $data['number'][0]['data'] = '10.45';
        $localizedConverter->convertToDefaultFormats($convertedData, [
            'decimal_separator' => '.',
            'date_format'       => 'yyyy-MM-dd'
        ])->willReturn($data);

        $violation = new ConstraintViolation(Argument::any(), Argument::any(), [], $product, 'number', '10,45');
        $violations = new ConstraintViolationList([$violation]);
        $localizedConverter->getViolations()->willReturn($violations);

        $stepExecution->incrementSummaryInfo('skip')->shouldBeCalled();

        $this
            ->shouldThrow('Akeneo\Component\Batch\Item\InvalidItemException')
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
        $stepExecution,
        ProductInterface $product,
        ConstraintViolationListInterface $violationList,
        JobParameters $jobParameters
    ) {
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('enabledComparison')->willReturn(true);
        $jobParameters->get('familyColumn')->willReturn('family');
        $jobParameters->get('categoriesColumn')->willReturn('categories');
        $jobParameters->get('groupsColumn')->willReturn('groups');
        $jobParameters->get('enabled')->willReturn(true);
        $jobParameters->get('decimalSeparator')->willReturn(',');
        $jobParameters->get('dateFormat')->willReturn('yyyy-MM-dd');

        $productRepository->getIdentifierProperties()->willReturn(['sku']);
        $productRepository->findOneByIdentifier('tshirt')->willReturn(false);
        $product->getId()->willReturn(42);

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
        $localizedConverter->convertToDefaultFormats($convertedData, [
            'decimal_separator' => ',',
            'date_format'       => 'yyyy-MM-dd'
        ])->willReturn($postConvertedData);
        $localizedConverter->getViolations()->willReturn($violationList);

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

        $stepExecution->incrementSummaryInfo('product_skipped_no_diff')->shouldBeCalled();

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
        $stepExecution,
        ProductInterface $product,
        ConstraintViolationListInterface $violationList,
        JobParameters $jobParameters
    ) {
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('enabledComparison')->willReturn(true);
        $jobParameters->get('familyColumn')->willReturn('family');
        $jobParameters->get('categoriesColumn')->willReturn('categories');
        $jobParameters->get('groupsColumn')->willReturn('groups');
        $jobParameters->get('enabled')->willReturn(true);
        $jobParameters->get('decimalSeparator')->willReturn('.');
        $jobParameters->get('dateFormat')->willReturn('yyyy-MM-dd');

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

        $localizedConverter->convertToDefaultFormats($convertedData, [
            'decimal_separator' => '.',
            'date_format'       => 'yyyy-MM-dd'
        ])->willReturn($convertedData);
        $localizedConverter->getViolations()->willReturn($violationList);

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

    function it_creates_a_product_with_sku_and_family_columns(
        $arrayConverter,
        $productRepository,
        $productBuilder,
        $productUpdater,
        $productValidator,
        $productFilter,
        $localizedConverter,
        $stepExecution,
        ProductInterface $product,
        ConstraintViolationListInterface $violationList,
        JobParameters $jobParameters
    ) {
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('enabledComparison')->willReturn(true);
        $jobParameters->get('familyColumn')->willReturn('family');
        $jobParameters->get('categoriesColumn')->willReturn('categories');
        $jobParameters->get('groupsColumn')->willReturn('groups');
        $jobParameters->get('enabled')->willReturn(true);
        $jobParameters->get('decimalSeparator')->willReturn('.');
        $jobParameters->get('dateFormat')->willReturn('yyyy-MM-dd');

        $productRepository->getIdentifierProperties()->willReturn(['sku']);
        $productRepository->findOneByIdentifier('tshirt')->willReturn(false);

        $productBuilder->createProduct('tshirt', 'Tshirt')->willReturn($product);

        $originalData = [
            'sku'    => 'tshirt',
            'family' => 'TShirt',
        ];
        $convertedData = [
            'sku' => [
                [
                    'locale' => null,
                    'scope'  =>  null,
                    'data'   => 'tshirt'
                ],
            ],
            'family' => 'Tshirt',
            'enabled' => true
        ];
        $converterOptions = [
            "mapping"           => ["family" => "family", "categories" => "categories", "groups" => "groups"],
            "default_values"    => ["enabled" => true],
            "with_associations" => false
        ];
        $arrayConverter
            ->convert($originalData, $converterOptions)
            ->willReturn($convertedData);

        $filteredData = [
            'family' => 'Tshirt',
            'enabled' => true
        ];

        $localizedConverter->convertToDefaultFormats($convertedData, [
            'decimal_separator' => '.',
            'date_format'       => 'yyyy-MM-dd'
        ])->willReturn($convertedData);
        $localizedConverter->getViolations()->willReturn($violationList);
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
