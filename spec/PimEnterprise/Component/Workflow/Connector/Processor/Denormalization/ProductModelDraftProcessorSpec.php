<?php

namespace spec\PimEnterprise\Component\Workflow\Connector\Processor\Denormalization;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\ProductModel\Filter\AttributeFilterInterface;
use PimEnterprise\Component\Workflow\Applier\DraftApplierInterface;
use PimEnterprise\Component\Workflow\Builder\EntityWithValuesDraftBuilderInterface;
use PimEnterprise\Component\Workflow\Model\EntityWithValuesDraftInterface;
use PimEnterprise\Component\Workflow\Repository\EntityWithValuesDraftRepositoryInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProductModelDraftProcessorSpec extends ObjectBehavior
{
    function let(
        IdentifiableObjectRepositoryInterface $repository,
        ObjectUpdaterInterface $updater,
        ValidatorInterface $validator,
        EntityWithValuesDraftBuilderInterface $productDraftBuilder,
        DraftApplierInterface $productDraftApplier,
        EntityWithValuesDraftRepositoryInterface $productDraftRepo,
        StepExecution $stepExecution,
        TokenStorageInterface $tokenStorage,
        AttributeFilterInterface $productModelAttributeFilter
    ) {
        $this->beConstructedWith(
            $repository,
            $updater,
            $validator,
            $productDraftBuilder,
            $productDraftApplier,
            $productDraftRepo,
            $tokenStorage,
            $productModelAttributeFilter
        );
        $this->setStepExecution($stepExecution);
    }

    function it_is_a_processor()
    {
        $this->shouldImplement('Akeneo\Tool\Component\Batch\Item\ItemProcessorInterface');
        $this->shouldImplement('Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface');
    }

    function it_creates_a_root_product_model_proposal(
        $repository,
        $updater,
        $validator,
        $productDraftBuilder,
        $stepExecution,
        $tokenStorage,
        $productModelAttributeFilter,
        ProductInterface $product,
        ConstraintViolationListInterface $violationList,
        EntityWithValuesDraftInterface $productDraft,
        JobExecution $jobExecution,
        JobInstance $jobInstance,
        TokenInterface $token
    ) {
        $repository->findOneByIdentifier('my-sku')->willReturn($product);
        $productDraft->setAllReviewStatuses(EntityWithValuesDraftInterface::CHANGE_TO_REVIEW)->willReturn($productDraft);

        $values = $this->getValues();
        $productModelAttributeFilter->filter($values)->willReturn($values);

        $updater
            ->update($product, $values)
            ->shouldBeCalled();

        $validator
            ->validate($product)
            ->willReturn($violationList);

        $productDraftBuilder->build($product, 'mary')->willReturn($productDraft);

        $tokenStorage->getToken()->willReturn($token);
        $token->getUsername()->willReturn('mary');

        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $stepExecution->getJobExecution()->willReturn($jobExecution);

        $this
            ->process($values)
            ->shouldReturn($productDraft);
    }

    function it_skips_a_proposal_if_there_is_no_identifier()
    {
        $values = $this->getValues();

        unset($values['code']);

        $this
            ->shouldThrow(new \InvalidArgumentException('Column "code" is expected'))
            ->during(
                'process',
                [$values]
            );
    }

    function it_skips_a_proposal_if_product_does_not_exist(
        $repository,
        $stepExecution
    ) {
        $repository->findOneByIdentifier('my-sku')->willReturn(null);

        $values = $this->getValues();

        $stepExecution->incrementSummaryInfo('skip')->shouldBeCalled();

        $this->setStepExecution($stepExecution);
        $stepExecution->getSummaryInfo('item_position')->willReturn(1);
        $stepExecution->incrementSummaryInfo('skip')->shouldBeCalled();

        $this->shouldThrow('Akeneo\Tool\Component\Batch\Item\InvalidItemException')
            ->during(
                'process',
                [$values]
            );
    }

    function it_skips_a_proposal_if_there_is_no_diff_between_product_and_proposal(
        $repository,
        $updater,
        $validator,
        $productDraftBuilder,
        $stepExecution,
        $tokenStorage,
        $productModelAttributeFilter,
        ProductInterface $product,
        ConstraintViolationListInterface $violationList,
        JobExecution $jobExecution,
        TokenInterface $token
    ) {
        $repository->findOneByIdentifier('my-sku')->willReturn($product);

        $values = $this->getValues();
        $productModelAttributeFilter->filter($values)->willReturn($values);

        $updater
            ->update($product, $values)
            ->shouldBeCalled();

        $validator
            ->validate($product)
            ->willReturn($violationList);

        $productDraftBuilder->build($product, 'mary')->willReturn(null);

        $tokenStorage->getToken()->willReturn($token);
        $token->getUsername()->willReturn('mary');

        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $stepExecution->incrementSummaryInfo('proposal_skipped')->shouldBeCalled();

        $this->process($values)->shouldReturn(null);
    }

    function getValues()
    {
        return [
            'code' => 'my-sku',
            'values' => [
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
            ],
        ];
    }
}
