<?php

namespace spec\PimEnterprise\Component\Workflow\Connector\Processor\Denormalization;

use Akeneo\Component\Batch\Job\JobParameters;
use Akeneo\Component\Batch\Model\JobExecution;
use Akeneo\Component\Batch\Model\JobInstance;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\Batch\Item\InvalidItemException;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ProductInterface;
use PimEnterprise\Component\Workflow\Applier\ProductDraftApplierInterface;
use PimEnterprise\Component\Workflow\Builder\ProductDraftBuilderInterface;
use PimEnterprise\Component\Workflow\Model\ProductDraft;
use PimEnterprise\Component\Workflow\Repository\ProductDraftRepositoryInterface;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProductDraftProcessorSpec extends ObjectBehavior
{
    function let(
        IdentifiableObjectRepositoryInterface $repository,
        ObjectUpdaterInterface $updater,
        ValidatorInterface $validator,
        ProductDraftBuilderInterface $productDraftBuilder,
        ProductDraftApplierInterface $productDraftApplier,
        ProductDraftRepositoryInterface $productDraftRepo,
        StepExecution $stepExecution
    ) {
        $this->beConstructedWith(
            $repository,
            $updater,
            $validator,
            $productDraftBuilder,
            $productDraftApplier,
            $productDraftRepo
        );
        $this->setStepExecution($stepExecution);
    }

    function it_is_a_configurable_step_execution_aware_processor()
    {
        $this->shouldBeAnInstanceOf('Akeneo\Component\Batch\Item\AbstractConfigurableStepElement');
        $this->shouldImplement('Akeneo\Component\Batch\Item\ItemProcessorInterface');
        $this->shouldImplement('Akeneo\Component\Batch\Step\StepExecutionAwareInterface');
    }

    function it_creates_a_proposal(
        $repository,
        $updater,
        $validator,
        $productDraftBuilder,
        $stepExecution,
        ProductInterface $product,
        ConstraintViolationListInterface $violationList,
        ProductDraft $productDraft,
        JobExecution $jobExecution,
        JobInstance $jobInstance
    ) {
        $repository->getIdentifierProperties()->willReturn(['sku']);
        $repository->findOneByIdentifier('my-sku')->willReturn($product);

        $values = $this->getValues();

        $updater
            ->update($product, $values['converted_values'])
            ->shouldBeCalled();

        $validator
            ->validate($product)
            ->willReturn($violationList);

        $productDraftBuilder->build($product, 'csv_product_proposal_import')->willReturn($productDraft);

        $jobInstance->getCode()->willReturn('csv_product_proposal_import');
        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $stepExecution->getJobExecution()->willReturn($jobExecution);

        $this
            ->process($values['converted_values'])
            ->shouldReturn($productDraft);
    }

    function it_skips_a_proposal_if_there_is_no_identifier(
        $repository,
        ProductInterface $product
    ) {
        $repository->getIdentifierProperties()->willReturn(['sku']);
        $repository->findOneByIdentifier('my-sku')->willReturn($product);

        $values = $this->getValues();

        unset($values['converted_values']['sku']);

        $this
            ->shouldThrow(new \InvalidArgumentException('Identifier property "sku" is expected'))
            ->during(
                'process',
                [$values['converted_values']]
            );
    }

    function it_skips_a_proposal_if_product_does_not_exist(
        $repository,
        $stepExecution
    ) {
        $repository->getIdentifierProperties()->willReturn(['sku']);
        $repository->findOneByIdentifier('my-sku')->willReturn(null);

        $values = $this->getValues();

        $stepExecution->incrementSummaryInfo('skip')->shouldBeCalled();



        $this->setStepExecution($stepExecution);
        $stepExecution->getSummaryInfo('read_lines')->willReturn(1);
        $stepExecution->incrementSummaryInfo('skip')->shouldBeCalled();

        $this->shouldThrow('Akeneo\Component\Batch\Item\InvalidItemException')
            ->during(
                'process',
                [$values['converted_values']]
            );
    }

    function it_skips_a_proposal_if_there_is_no_diff_between_product_and_proposal(
        $repository,
        $updater,
        $validator,
        $productDraftBuilder,
        $stepExecution,
        ProductInterface $product,
        ConstraintViolationListInterface $violationList,
        JobExecution $jobExecution,
        JobInstance $jobInstance
    ) {
        $repository->getIdentifierProperties()->willReturn(['sku']);
        $repository->findOneByIdentifier('my-sku')->willReturn($product);

        $values = $this->getValues();

        $updater
            ->update($product, $values['converted_values'])
            ->shouldBeCalled();

        $validator
            ->validate($product)
            ->willReturn($violationList);

        $productDraftBuilder->build($product, 'csv_product_proposal_import')->willReturn(null);

        $jobInstance->getCode()->willReturn('csv_product_proposal_import');
        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $stepExecution->incrementSummaryInfo('proposal_skipped')->shouldBeCalled();

        $this->process($values['converted_values'])->shouldReturn(null);
    }

    function getValues()
    {
        return [
            'converted_values' => [
                'sku'          => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'data' => 'my-sku'
                    ]
                ],
                'main_color'   => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'data'   =>'white'
                    ]
                ],
                'description'  => [
                    [
                        'locale' => 'fr_FR',
                        'scope'  => 'ecommerce',
                        'data'   => '<p>description</p>'
                    ],
                    [
                        'locale' => 'en_US',
                        'scope'  => 'ecommerce',
                        'data'   => '<p>description</p>'
                    ],
                ],
                'release_date' => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'data'   => '1977-08-19'
                    ]
                ],
                'price'        => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'data'   => [
                            [
                                'currency' => 'EUR',
                                'data'     => '10.25'
                            ],
                            [
                                'currency' => 'USD',
                                'data'     => '11.5'
                            ],
                        ]
                    ]
                ],
            ]
        ];
    }
}
