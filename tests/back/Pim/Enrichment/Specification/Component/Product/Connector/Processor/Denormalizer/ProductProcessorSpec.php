<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\Denormalizer;

use Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\Denormalizer\MediaStorer;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Tool\Component\Batch\Item\InvalidItemException;
use Akeneo\Tool\Component\Batch\Item\ItemProcessorInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Comparator\Filter\FilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\ProductModel\Filter\AttributeFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\EntityWithFamilyVariant\AddParent;
use Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\Denormalizer\FindProductToImport;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProductProcessorSpec extends ObjectBehavior
{
    function let(
        IdentifiableObjectRepositoryInterface $productRepository,
        FindProductToImport $productToImport,
        AddParent $addParent,
        ObjectUpdaterInterface $productUpdater,
        ValidatorInterface $productValidator,
        StepExecution $stepExecution,
        ObjectDetacherInterface $productDetacher,
        FilterInterface $productFilter,
        AttributeFilterInterface $productAttributeFilter,
        MediaStorer $mediaStorer
    ) {
        $this->beConstructedWith(
            $productRepository,
            $productToImport,
            $addParent,
            $productUpdater,
            $productValidator,
            $productDetacher,
            $productFilter,
            $productAttributeFilter,
            $mediaStorer
        );
        $this->setStepExecution($stepExecution);
    }

    function it_is_a_processor()
    {
        $this->shouldImplement(ItemProcessorInterface::class);
        $this->shouldImplement(StepExecutionAwareInterface::class);
    }

    function it_updates_an_existing_product(
        $productRepository,
        $productUpdater,
        $productValidator,
        $productFilter,
        $stepExecution,
        $productAttributeFilter,
        $productToImport,
        $addParent,
        $mediaStorer,
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
        $productToImport->fromFlatData('tshirt', 'Summer Tshirt')->willReturn($product);
        $product->getId()->willReturn(42);

        $addParent->to($product, '')->willReturn($product);

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

        $productAttributeFilter->filter(Argument::type('array'))->willReturn($convertedData);

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

        $mediaStorer->store($filteredData['values'])->willReturn($filteredData['values']);

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
        $productToImport,
        $productUpdater,
        $productValidator,
        $productFilter,
        $stepExecution,
        $productAttributeFilter,
        $addParent,
        $mediaStorer,
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
        $productToImport->fromFlatData('tshirt', 'Tshirt')->willReturn($product);
        $product->getId()->willReturn(42);

        $addParent->to($product, '')->willReturn($product);

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

        $productAttributeFilter->filter(Argument::type('array'))->willReturn($convertedData);

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

        $mediaStorer->store($filteredData['values'])->willReturn($filteredData['values']);

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
        $productToImport,
        $productUpdater,
        $productValidator,
        $productFilter,
        $stepExecution,
        $productAttributeFilter,
        $addParent,
        $mediaStorer,
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
        $productToImport->fromFlatData('tshirt', 'Tshirt')->willReturn($product);
        $product->getId()->willReturn(42);

        $addParent->to($product, '')->willReturn($product);

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

        $productAttributeFilter->filter(Argument::type('array'))->willReturn($convertedData);

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

        $mediaStorer->store($filteredData['values'])->willReturn($filteredData['values']);

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
            ->shouldThrow(InvalidItemException::class)
            ->during(
                'process',
                [$convertedData]
            );
    }

    function it_skips_a_product_when_update_fails(
        $productRepository,
        $productToImport,
        $productUpdater,
        $productDetacher,
        $productFilter,
        $stepExecution,
        $productAttributeFilter,
        $addParent,
        $mediaStorer,
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
        $productToImport->fromFlatData('tshirt', 'Tshirt')->willReturn($product);
        $stepExecution->getSummaryInfo('item_position')->shouldBeCalled();

        $addParent->to($product, '')->willReturn($product);

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

        $productAttributeFilter->filter(Argument::type('array'))->willReturn($convertedData);

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

        $mediaStorer->store($filteredData['values'])->willReturn($filteredData['values']);

        $productFilter->filter($product, $filteredData)->willReturn($filteredData);

        $productUpdater
            ->update($product, $filteredData)
            ->willThrow(new InvalidPropertyException('family', 'value', 'className', 'family does not exists'));

        $productDetacher->detach($product)->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('skip')->shouldBeCalled();

        $this
            ->shouldThrow(InvalidItemException::class)
            ->during(
                'process',
                [$convertedData]
            );
    }

    function it_skips_a_product_when_object_is_invalid(
        $productRepository,
        $productToImport,
        $productUpdater,
        $productValidator,
        $productDetacher,
        $productFilter,
        $stepExecution,
        $productAttributeFilter,
        $addParent,
        $mediaStorer,
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
        $productToImport->fromFlatData('tshirt', 'Tshirt')->willReturn($product);

        $stepExecution->getSummaryInfo('item_position')->shouldBeCalled();
        $addParent->to($product, '')->willReturn($product);

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

        $productAttributeFilter->filter(Argument::type('array'))->willReturn($convertedData);

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

        $mediaStorer->store($filteredData['values'])->willReturn($filteredData['values']);

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
            ->shouldThrow(InvalidItemException::class)
            ->during(
                'process',
                [$convertedData]
            );
    }

    function it_skips_a_product_when_there_is_nothing_to_update(
        $productRepository,
        $productToImport,
        $productUpdater,
        $productDetacher,
        $productFilter,
        $stepExecution,
        $productAttributeFilter,
        $addParent,
        $mediaStorer,
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
        $productToImport->fromFlatData('tshirt', 'Tshirt')->willReturn($product);
        $product->getId()->willReturn(1);
        $addParent->to($product, '')->willReturn($product);

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

        $productAttributeFilter->filter(Argument::type('array'))->willReturn($convertedData);

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

        $mediaStorer->store($filteredData['values'])->willReturn($filteredData['values']);

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
        $productToImport,
        $productUpdater,
        $productValidator,
        $productFilter,
        $stepExecution,
        $productAttributeFilter,
        $addParent,
        $mediaStorer,
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
        $productToImport->fromFlatData('tshirt', 'Summer Tshirt')->willReturn($product);
        $product->getId()->willReturn(42);

        $addParent->to($product, '')->willReturn($product);

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

        $productAttributeFilter->filter(Argument::type('array'))->willReturn($convertedData);

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

        $mediaStorer->store($filteredData['values'])->willReturn($filteredData['values']);

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
        $productToImport,
        $productUpdater,
        $productValidator,
        $productFilter,
        $stepExecution,
        $productAttributeFilter,
        $addParent,
        $mediaStorer,
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
        $productToImport->fromFlatData('tshirt', 'Tshirt')->willReturn($product);

        $addParent->to($product, '')->willReturn($product);

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

        $productAttributeFilter->filter(Argument::type('array'))->willReturn($convertedData);

        $filteredData = [
            'family' => 'Tshirt',
            'enabled' => true,
            'values' => [],
        ];

        $mediaStorer->store($filteredData['values'])->willReturn($filteredData['values']);

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

    function it_fetches_family_of_the_product_if_the_column_is_not_set(
        $productRepository,
        $productToImport,
        $productUpdater,
        $productValidator,
        $productFilter,
        $stepExecution,
        $productAttributeFilter,
        $addParent,
        $mediaStorer,
        ProductInterface $product,
        ProductInterface $productInDB,
        ConstraintViolationListInterface $violationList,
        JobParameters $jobParameters,
        FamilyInterface $family
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

        $productRepository->findOneByIdentifier('tshirt')->willReturn($productInDB);
        $productInDB->getFamily()->willReturn($family);
        $family->getCode()->willReturn('Tshirt');

        $productToImport->fromFlatData('tshirt', 'Tshirt')->willReturn($product);

        $addParent->to($product, '')->willReturn($product);

        $originalItem = [
            'identifier' => 'tshirt',
            'values' => [
                'sku' => [
                    [
                        'locale' => null,
                        'scope' => null,
                        'data' => 'tshirt',
                    ],
                ],
                'not_in_family' => [
                    [
                        'locale' => null,
                        'scope' => null,
                        'data' => 'test',
                    ],
                ],
            ],
            'enabled' => true,
        ];

        $filteredAttributesItem = [
            'identifier' => 'tshirt',
            'family' => 'Tshirt',
            'values' => [
                'sku' => [
                    [
                        'locale' => null,
                        'scope' => null,
                        'data' => 'tshirt',
                    ],
                ],
            ],
            'enabled' => true,
        ];

        $productAttributeFilter->filter(Argument::type('array'))->willReturn($filteredAttributesItem);

        $filteredData = [
            'family' => 'Tshirt',
            'enabled' => true,
            'values' => [],
        ];

        $mediaStorer->store($filteredData['values'])->willReturn($filteredData['values']);

        $productFilter->filter($product, $filteredData)->willReturn($filteredData);

        $productUpdater
            ->update($product, $filteredData)
            ->shouldBeCalled();

        $productValidator
            ->validate($product)
            ->willReturn($violationList);

        $this
            ->process($originalItem)
            ->shouldReturn($product);
    }
}
