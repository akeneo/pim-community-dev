<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\Denormalizer;

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
use Akeneo\Pim\Enrichment\Component\Product\Model\AssociationInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProductAssociationProcessorSpec extends ObjectBehavior
{
    function let(
        IdentifiableObjectRepositoryInterface $productRepository,
        ObjectUpdaterInterface $productUpdater,
        ValidatorInterface $productValidator,
        StepExecution $stepExecution,
        FilterInterface $productAssocFilter,
        ObjectDetacherInterface $productDetacher
    ) {
        $this->beConstructedWith(
            $productRepository,
            $productUpdater,
            $productValidator,
            $productAssocFilter,
            $productDetacher
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
        $productAssocFilter,
        $stepExecution,
        ProductInterface $product,
        AssociationInterface $association,
        ConstraintViolationListInterface $violationList,
        JobParameters $jobParameters
    ) {
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('enabledComparison')->willReturn(true);

        $productRepository->getIdentifierProperties()->willReturn(['sku']);
        $productRepository->findOneByIdentifier(Argument::any())->willReturn($product);
        $product->getId()->willReturn(42);

        $convertedData = [
            'identifier'   => 'tshirt',
            'values'       => [
                'sku' => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'data'   => 'tshirt'
                    ],
                ]
            ],
            'associations' => [
                'XSELL' => [
                    'groups'  => ['akeneo_tshirt', 'oro_tshirt'],
                    'product' => ['AKN_TS', 'ORO_TS']
                ]
            ]
        ];

        $filteredData = [
            'associations' => [
                'XSELL' => [
                    'groups'  => ['akeneo_tshirt', 'oro_tshirt'],
                    'product' => ['AKN_TS', 'ORO_TS']
                ]
            ]
        ];

        unset($filteredData['associations']['XSELL']['groups']);
        $productAssocFilter->filter($product, $convertedData)
            ->shouldBeCalled()
            ->willReturn($filteredData);

        $productUpdater
            ->update($product, $filteredData)
            ->shouldBeCalled();

        $product->getAssociations()->willReturn([$association]);
        $productValidator
            ->validate($association)
            ->willReturn($violationList);

        $this
            ->process($convertedData)
            ->shouldReturn($product);
    }

    function it_skips_a_product_when_update_fails(
        $productRepository,
        $productUpdater,
        $productAssocFilter,
        $stepExecution,
        $productDetacher,
        ProductInterface $product,
        JobParameters $jobParameters
    ) {
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('enabledComparison')->willReturn(true);
        $stepExecution->getSummaryInfo('item_position')->shouldBeCalled();

        $productRepository->getIdentifierProperties()->willReturn(['sku']);
        $productRepository->findOneByIdentifier(Argument::any())->willReturn($product);
        $product->getId()->willReturn(42);

        $convertedData = [
            'identifier'   => 'tshirt',
            'values'       => [
                'sku' => [
                    [
                        'locale' => null,
                        'scope' =>  null,
                        'data' => 'tshirt'
                    ],
                ]
            ],
            'associations' => [
                'NOT_FOUND' => [
                    'groups'  => ['akeneo_tshirt', 'oro_tshirt'],
                    'product' => ['AKN_TS', 'ORO_TS']
                ]
            ]
        ];

        $filteredData = [
            'associations' => [
                'NOT_FOUND' => [
                    'groups'  => ['akeneo_tshirt', 'oro_tshirt'],
                    'product' => ['AKN_TS', 'ORO_TS']
                ]
            ]
        ];

        $productAssocFilter->filter($product, $convertedData)
            ->shouldBeCalled()
            ->willReturn($filteredData);

        $productUpdater
            ->update($product, $filteredData)
            ->willThrow(new InvalidPropertyException('associations', 'value', 'className', 'association does not exists'));

        $stepExecution->incrementSummaryInfo('skip')->shouldBeCalled();
        $this->setStepExecution($stepExecution);

        $productDetacher->detach($product)->shouldBeCalled();

        $this
            ->shouldThrow(InvalidItemException::class)
            ->during(
                'process',
                [$convertedData]
            );
    }

    function it_skips_a_product_when_association_is_invalid(
        $productRepository,
        $productUpdater,
        $productValidator,
        $productAssocFilter,
        $stepExecution,
        $productDetacher,
        AssociationInterface $association,
        ProductInterface $product,
        JobParameters $jobParameters
    ) {
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $stepExecution->getSummaryInfo('item_position')->shouldBeCalled();
        $jobParameters->get('enabledComparison')->willReturn(true);
        $productRepository->getIdentifierProperties()->willReturn(['sku']);
        $productRepository->findOneByIdentifier(Argument::any())->willReturn($product);
        $product->getId()->willReturn(42);

        $convertedData = [
            'identifier'   => 'tshirt',
            'values'       => [
                'sku' => [
                    [
                        'locale' => null,
                        'scope' =>  null,
                        'data' => 'tshirt'
                    ],
                ]
            ],
            'associations' => [
                'XSELL' => [
                    'groups'  => ['akeneo_tshirt', 'oro_tshirt'],
                    'product' => ['AKN_TS', 'ORO_TS']
                ]
            ]
        ];

        $filteredData = [
            'associations' => [
                'XSELL' => [
                    'groups'  => ['akeneo_tshirt', 'oro_tshirt'],
                    'product' => ['AKN_TS', 'ORO_TS']
                ]
            ]
        ];

        $productAssocFilter->filter($product, $convertedData)
            ->shouldBeCalled()
            ->willReturn($filteredData);

        $productUpdater
            ->update($product, $filteredData)
            ->shouldBeCalled();

        $violation = new ConstraintViolation('There is a small problem with option code', 'foo', [], 'bar', 'code', 'mycode');
        $violations = new ConstraintViolationList([$violation]);
        $product->getAssociations()->willReturn([$association]);
        $productValidator
            ->validate($association)
            ->willReturn($violations);

        $stepExecution->incrementSummaryInfo('skip')->shouldBeCalled();
        $this->setStepExecution($stepExecution);

        $productDetacher->detach($product)->shouldBeCalled();

        $this
            ->shouldThrow(InvalidItemException::class)
            ->during(
                'process',
                [$convertedData]
            );
    }

    function it_skips_a_product_when_there_is_nothing_to_update(
        $productRepository,
        $productUpdater,
        $productAssocFilter,
        $stepExecution,
        $productDetacher,
        ProductInterface $product,
        JobParameters $jobParameters
    ) {
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('enabledComparison')->willReturn(true);

        $productRepository->getIdentifierProperties()->willReturn(['sku']);
        $productRepository->findOneByIdentifier(Argument::any())->willReturn($product);
        $product->getId()->willReturn(42);

        $convertedData = [
            'identifier'   => 'tshirt',
            'values'       => [
                'sku' => [
                    [
                        'locale' => null,
                        'scope' =>  null,
                        'data' => 'tshirt'
                    ],
                ]
            ],
            'associations' => [
                'XSELL' => [
                    'groups'  => ['akeneo_tshirt', 'oro_tshirt'],
                    'product' => ['AKN_TS', 'ORO_TSH']
                ]
            ]
        ];

        $filteredData = [
            'associations' => [
                'XSELL' => [
                    'groups'  => ['akeneo_tshirt', 'oro_tshirt'],
                    'product' => ['AKN_TS', 'ORO_TSH']
                ]
            ]
        ];

        $productAssocFilter->filter($product, $convertedData)
            ->shouldBeCalled()
            ->willReturn([]);

        $productUpdater
            ->update($product, $filteredData)
            ->shouldNotBeCalled();

        $stepExecution->incrementSummaryInfo('product_skipped_no_diff')->shouldBeCalled();
        $this->setStepExecution($stepExecution);

        $productDetacher->detach($product)->shouldBeCalled();

        $this->process($convertedData)
            ->shouldReturn(null);
    }

    function it_skips_a_product_when_there_is_no_association_to_update(
        $productRepository,
        $productUpdater,
        $productAssocFilter,
        $stepExecution,
        JobParameters $jobParameters
    ) {
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('enabledComparison')->willReturn(false);

        $productRepository->getIdentifierProperties()->shouldNotBeCalled();
        $productRepository->findOneByIdentifier(Argument::any())->shouldNotBeCalled();

        $convertedData = [
            'identifier' => 'tshirt',
            'sku' => [
                [
                    'locale' => null,
                    'scope' =>  null,
                    'data' => 'tshirt'
                ],
            ],
            'associations' => []
        ];

        $productAssocFilter->filter(Argument::any())->shouldNotBeCalled();
        $productUpdater->update(Argument::any())->shouldNotBeCalled();

        $stepExecution->incrementSummaryInfo('product_skipped_no_associations')->shouldBeCalled();
        $this->setStepExecution($stepExecution);
        $this->process($convertedData)->shouldReturn(null);
    }
}
