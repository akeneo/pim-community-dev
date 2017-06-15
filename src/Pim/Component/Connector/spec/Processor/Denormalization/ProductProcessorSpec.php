<?php

namespace spec\Pim\Component\Connector\Processor\Denormalization;

use Akeneo\Component\Batch\Job\JobParameters;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Builder\ProductBuilderInterface;
use Pim\Component\Catalog\Comparator\Filter\ProductFilterInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProductProcessorSpec extends ObjectBehavior
{
    function let(
        IdentifiableObjectRepositoryInterface $productRepository,
        ProductBuilderInterface $productBuilder,
        ObjectUpdaterInterface $productUpdater,
        ValidatorInterface $productValidator,
        StepExecution $stepExecution,
        ObjectDetacherInterface $productDetacher,
        ProductFilterInterface $productFilter
    ) {
        $this->beConstructedWith(
            $productRepository,
            $productBuilder,
            $productUpdater,
            $productValidator,
            $productDetacher,
            $productFilter
        );
        $this->setStepExecution($stepExecution);
    }

    function it_is_a_processor()
    {
        $this->shouldImplement('Akeneo\Component\Batch\Item\ItemProcessorInterface');
        $this->shouldImplement('Akeneo\Component\Batch\Step\StepExecutionAwareInterface');
    }

    function it_updates_an_existing_product(
        $productRepository,
        $productUpdater,
        $productValidator,
        $productFilter,
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
        $jobParameters->get('decimalSepara7tor')->willReturn('.');
        $jobParameters->get('dateFormat')->willReturn('yyyy-MM-dd');

        $productRepository->getIdentifierProperties()->willReturn(['sku']);
        $productRepository->findOneByIdentifier(Argument::any())->willReturn($product);
        $product->getId()->willReturn(42);

        $convertedData = [
            'identifier' => 'tshirt',
            'family'     => 'Summer Tshirt',
            'values'     => [
                'sku'         => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'data'   => 'tshirt'
                    ],
                ],
                'name'        => [
                    [
                        'locale' => 'fr_FR',
                        'scope'  => null,
                        'data'   => 'Mon super beau t-shirt'
                    ],
                    [
                        'locale' => 'en_US',
                        'scope'  => null,
                        'data'   => 'My very awesome T-shirt'
                    ]
                ],
                'description' => [
                    [
                        'locale' => 'en_US',
                        'scope'  => 'mobile',
                        'data'   => 'My awesome description'
                    ]
                ]
            ]
        ];

        $filteredData = [
            'family' => 'Summer Tshirt',
            'values' => [
                'name'        => [
                    [
                        'locale' => 'fr_FR',
                        'scope'  => null,
                        'data'   => 'Mon super beau t-shirt'
                    ],
                    [
                        'locale' => 'en_US',
                        'scope'  => null,
                        'data'   => 'My very awesome T-shirt'
                    ]
                ],
                'description' => [
                    [
                        'locale' => 'en_US',
                        'scope'  => 'mobile',
                        'data'   => 'My awesome description'
                    ]
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
            ->process($convertedData)
            ->shouldReturn($product);
    }

    function it_updates_an_existing_product_with_filtered_values(
        $productRepository,
        $productUpdater,
        $productValidator,
        $productFilter,
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

        $convertedData = [
            'identifier' => 'tshirt',
            'family'     => 'Tshirt',
            'values'     => [
                'sku'         => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'data'   => 'tshirt'
                    ],
                ],
                'name'        => [
                    [
                        'locale' => 'fr_FR',
                        'scope'  => null,
                        'data'   => 'Mon super beau t-shirt'
                    ],
                    [
                        'locale' => 'en_US',
                        'scope'  => null,
                        'data'   => 'My awesome T-shirt'
                    ]
                ],
                'description' => [
                    [
                        'locale' => 'en_US',
                        'scope'  => 'mobile',
                        'data'   => 'My awesome description'
                    ]
                ]
            ]
        ];

        $preFilteredData = $filteredData = [
            'family' => 'Tshirt',
            'values' => [
                'name'        => [
                    [
                        'locale' => 'fr_FR',
                        'scope'  => null,
                        'data'   => 'Mon super beau t-shirt'
                    ],
                    [
                        'locale' => 'en_US',
                        'scope'  => null,
                        'data'   => 'My awesome T-shirt'
                    ]
                ],
                'description' => [
                    [
                        'locale' => 'en_US',
                        'scope'  => 'mobile',
                        'data'   => 'My awesome description'
                    ]
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
            ->process($convertedData)
            ->shouldReturn($product);
    }

    function it_updates_an_existing_product_without_filtered_values(
        $productRepository,
        $productUpdater,
        $productValidator,
        $productFilter,
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

        $convertedData = [
            'identifier' => 'tshirt',
            'family'     => 'Tshirt',
            'values'     => [
                'sku' => [
                    [
                        'locale' => null,
                        'scope' =>  null,
                        'data' => 'tshirt'
                    ],
                ],
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
            ]
        ];

        $filteredData = [
            'family' => 'Tshirt',
            'values' => [
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
            ->process($convertedData)
            ->shouldReturn($product);
    }

    function it_creates_a_product(
        $productRepository,
        $productBuilder,
        $productUpdater,
        $productValidator,
        $productFilter,
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

        $convertedData = [
            'identifier' => 'tshirt',
            'enabled' => true,
            'family' => 'Tshirt',
            'values' => [
                'sku' => [
                    [
                        'locale' => null,
                        'scope' =>  null,
                        'data' => 'tshirt'
                    ],
                ],
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
                ],
            ]
        ];

        $filteredData = [
            'family' => 'Tshirt',
            'values' => [
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
                ],
            ],
            'enabled' => true
        ];

        $productFilter->filter($product, $filteredData)->willReturn($filteredData);

        $productUpdater
            ->update($product, $filteredData)
            ->shouldBeCalled();

        $productValidator
            ->validate($product)
            ->willReturn($violationList);

        $this
            ->process($convertedData)
            ->shouldReturn($product);
    }

    function it_skips_a_product_when_identifier_is_empty($stepExecution, JobParameters $jobParameters)
    {
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('enabledComparison')->willReturn(true);
        $jobParameters->get('familyColumn')->willReturn('family');
        $jobParameters->get('categoriesColumn')->willReturn('categories');
        $jobParameters->get('groupsColumn')->willReturn('groups');
        $jobParameters->get('enabled')->willReturn(true);
        $jobParameters->get('decimalSeparator')->willReturn('.');
        $jobParameters->get('dateFormat')->willReturn('yyyy-MM-dd');

        $convertedData = [
            'identifier' => null,
            'values'     => [
                'sku' => [
                    [
                        'locale' => null,
                        'scope' =>  null,
                        'data' => null
                    ],
                ]
            ],
            'family' => 'Tshirt'
        ];

        $stepExecution->incrementSummaryInfo('skip')->shouldBeCalled();
        $stepExecution->getSummaryInfo('item_position')->shouldBeCalled();

        $this
            ->shouldThrow('Akeneo\Component\Batch\Item\InvalidItemException')
            ->during(
                'process',
                [$convertedData]
            );
    }

    function it_skips_a_product_when_update_fails(
        $productRepository,
        $productBuilder,
        $productUpdater,
        $productDetacher,
        $productFilter,
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
        $productRepository->findOneByIdentifier('tshirt')->willReturn(false);
        $stepExecution->getSummaryInfo('item_position')->shouldBeCalled();

        $productBuilder->createProduct('tshirt', 'Tshirt')->willReturn($product);

        $convertedData = [
            'identifier' => 'tshirt',
            'family' => 'Tshirt',
            'values'     => [
                'sku' => [
                    [
                        'locale' => null,
                        'scope' =>  null,
                        'data' => 'tshirt'
                    ],
                ],
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
                ],
            ],
            'enabled' => true
        ];

        $filteredData = [
            'family' => 'Tshirt',
            'values' => [
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
                ],
            ],
            'enabled' => true
        ];

        $productFilter->filter($product, $filteredData)->willReturn($filteredData);

        $productUpdater
            ->update($product, $filteredData)
            ->willThrow(new InvalidPropertyException('family', 'value', 'className', 'family does not exists'));

        $productDetacher->detach($product)->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('skip')->shouldBeCalled();

        $this
            ->shouldThrow('Akeneo\Component\Batch\Item\InvalidItemException')
            ->during(
                'process',
                [$convertedData]
            );
    }

    function it_skips_a_product_when_object_is_invalid(
        $productRepository,
        $productBuilder,
        $productUpdater,
        $productValidator,
        $productDetacher,
        $productFilter,
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
        $stepExecution->getSummaryInfo('item_position')->shouldBeCalled();

        $convertedData = [
            'identifier' => 'tshirt',
            'family' => 'Tshirt',
            'values'     => [
                'sku' => [
                    [
                        'locale' => null,
                        'scope' =>  null,
                        'data' => 'tshirt'
                    ],
                ],
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
                ],
            ],
            'enabled' => true
        ];

        $filteredData = [
            'family' => 'Tshirt',
            'values' => [
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
                ],
            ],
            'enabled' => true
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
                [$convertedData]
            );
    }

    function it_skips_a_product_when_there_is_nothing_to_update(
        $productRepository,
        $productUpdater,
        $productDetacher,
        $productFilter,
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
        $productRepository->findOneByIdentifier('tshirt')->willReturn($product);
        $product->getId()->willReturn(1);

        $convertedData = [
            'identifier' => 'tshirt',
            'family' => 'Tshirt',
            'values'     => [
                'sku' => [
                    [
                        'locale' => null,
                        'scope' =>  null,
                        'data' => 'tshirt'
                    ],
                ],
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
            ]
        ];

        $filteredData = [
            'family' => 'Tshirt',
            'values' => [
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
            ]
        ];

        $productFilter->filter($product, $filteredData)->willReturn([]);

        $productUpdater
            ->update($product, $filteredData)->shouldNotBeCalled();

        $productDetacher->detach($product)->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('product_skipped_no_diff')->shouldBeCalled();

        $this
            ->process($convertedData)
            ->shouldReturn(null);
    }

    function it_updates_an_existing_product_and_does_not_change_his_state(
        $productRepository,
        $productUpdater,
        $productValidator,
        $productFilter,
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

        $convertedData = [
            'identifier' => 'tshirt',
            'family' => 'Summer Tshirt',
            'values'     => [
                'sku' => [
                    [
                        'locale' => null,
                        'scope' =>  null,
                        'data' => 'tshirt'
                    ],
                ],
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
            ],
            'enabled' => false,
        ];

        $filteredData = [
            'family' => 'Summer Tshirt',
            'values' => [
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
            ],
            'enabled' => false,
        ];

        $productFilter->filter($product, $filteredData)->willReturn($filteredData);

        $productUpdater
            ->update($product, $filteredData)
            ->shouldBeCalled();

        $productValidator
            ->validate($product)
            ->willReturn($violationList);

        $this
            ->process($convertedData)
            ->shouldReturn($product);
    }

    function it_creates_a_product_with_sku_and_family_columns(
        $productRepository,
        $productBuilder,
        $productUpdater,
        $productValidator,
        $productFilter,
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

        $convertedData = [
            'identifier' => 'tshirt',
            'family' => 'Tshirt',
            'values'     => [
                'sku' => [
                    [
                        'locale' => null,
                        'scope'  =>  null,
                        'data'   => 'tshirt'
                    ],
                ],
            ],
            'enabled' => true
        ];

        $filteredData = [
            'family' => 'Tshirt',
            'enabled' => true,
            'values' => [],
        ];

        $productFilter->filter($product, $filteredData)->willReturn($filteredData);

        $productUpdater
            ->update($product, $filteredData)
            ->shouldBeCalled();

        $productValidator
            ->validate($product)
            ->willReturn($violationList);

        $this
            ->process($convertedData)
            ->shouldReturn($product);
    }
}
