<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Component\Connector\Processor\Denormalization;

use Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\Denormalizer\MediaStorer;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Applier\DraftApplierInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Builder\EntityWithValuesDraftBuilderInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Factory\PimUserDraftSourceFactory;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\DraftSource;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Repository\EntityWithValuesDraftRepositoryInterface;
use Akeneo\Tool\Component\Batch\Item\ExecutionContext;
use Akeneo\Tool\Component\Batch\Item\InvalidItemException;
use Akeneo\Tool\Component\Batch\Item\ItemProcessorInterface;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProductDraftProcessorSpec extends ObjectBehavior
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
        MediaStorer $mediaStorer,
        PimUserDraftSourceFactory $draftSourceFactory
    ) {
        $this->beConstructedWith(
            $repository,
            $updater,
            $validator,
            $productDraftBuilder,
            $productDraftApplier,
            $productDraftRepo,
            $tokenStorage,
            $mediaStorer,
            $draftSourceFactory
        );
        $this->setStepExecution($stepExecution);
    }

    function it_is_a_processor()
    {
        $this->shouldImplement(ItemProcessorInterface::class);
        $this->shouldImplement(StepExecutionAwareInterface::class);
    }

    function it_creates_a_proposal(
        $repository,
        $updater,
        $validator,
        $productDraftBuilder,
        $stepExecution,
        $tokenStorage,
        $mediaStorer,
        $draftSourceFactory,
        ProductInterface $product,
        ConstraintViolationListInterface $violationList,
        EntityWithValuesDraftInterface $productDraft,
        JobExecution $jobExecution,
        JobInstance $jobInstance,
        TokenInterface $token,
        UserInterface $user,
        DraftSource $draftSource,
        ExecutionContext $executionContext
    ) {
        $this->prepareDraftSource($tokenStorage, $token, $user, $draftSource, $draftSourceFactory);

        $repository->findOneByIdentifier('my-sku')->willReturn($product);
        $product->isVariant()->willReturn(false);
        $productDraft->setAllReviewStatuses(EntityWithValuesDraftInterface::CHANGE_TO_REVIEW)->willReturn($productDraft);

        $values = $this->getValues();

        $mediaStorer->store($values['values'])->willReturn($values['values']);

        $updater
            ->update($product, $values)
            ->shouldBeCalled();

        $validator
            ->validate($product)
            ->willReturn($violationList);

        $productDraftBuilder->build($product, $draftSource)->willReturn($productDraft);

        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $stepExecution->getExecutionContext()->willReturn($executionContext);

        $this
            ->process($values)
            ->shouldReturn($productDraft);
    }

    function it_takes_into_account_the_last_proposal_when_there_are_several_proposals_on_the_same_attribute_of_the_same_product(
        $repository,
        $updater,
        $validator,
        $productDraftBuilder,
        $stepExecution,
        $tokenStorage,
        $mediaStorer,
        $draftSourceFactory,
        ProductInterface $product,
        ConstraintViolationListInterface $violationList,
        EntityWithValuesDraftInterface $productDraft,
        EntityWithValuesDraftInterface $previousProductDraft,
        JobExecution $jobExecution,
        JobInstance $jobInstance,
        TokenInterface $token,
        UserInterface $user,
        DraftSource $draftSource,
        ExecutionContext $executionContext,
        WriteValueCollection $draftValues
    )
    {
        $this->prepareDraftSource($tokenStorage, $token, $user, $draftSource, $draftSourceFactory);

        $repository->findOneByIdentifier('my-sku')->willReturn($product);
        $product->isVariant()->willReturn(false);
        $productDraft->setAllReviewStatuses(EntityWithValuesDraftInterface::CHANGE_TO_REVIEW)->willReturn($productDraft);

        $values = $this->getValues();

        $mediaStorer->store($values['values'])->willReturn($values['values']);

        $updater
            ->update($product, $values)
            ->shouldBeCalled();

        $validator
            ->validate($product)
            ->willReturn($violationList);

        $productDraftBuilder->build($product, $draftSource)->willReturn($productDraft);

        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $stepExecution->getExecutionContext()->willReturn($executionContext);
        $executionContext->get('processed_items_batch')->willReturn(['my-sku' => $previousProductDraft]);

        $productDraft->getValues()->willReturn($draftValues);
        $previousProductDraft->setValues($draftValues)->shouldBeCalled();

        $productDraft->getChanges()->willReturn($values);
        $previousProductDraft->setChanges($values)->shouldBeCalled();


        $this
            ->process($values)
            ->shouldReturn(null);
    }

    function it_skips_a_proposal_if_there_is_no_identifier()
    {
        $values = $this->getValues();

        unset($values['identifier']);

        $this
            ->shouldThrow(new \InvalidArgumentException('Column "identifier" is expected'))
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

        $this->shouldThrow(InvalidItemException::class)
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
        $mediaStorer,
        $draftSourceFactory,
        ProductInterface $product,
        ConstraintViolationListInterface $violationList,
        JobExecution $jobExecution,
        TokenInterface $token,
        UserInterface $user,
        DraftSource $draftSource
    ) {
        $this->prepareDraftSource($tokenStorage, $token, $user, $draftSource, $draftSourceFactory);

        $repository->findOneByIdentifier('my-sku')->willReturn($product);
        $product->isVariant()->willReturn(false);

        $values = $this->getValues();

        $mediaStorer->store($values['values'])->willReturn($values['values']);

        $updater
            ->update($product, $values)
            ->shouldBeCalled();

        $validator
            ->validate($product)
            ->willReturn($violationList);

        $productDraftBuilder->build($product, $draftSource)->willReturn(null);

        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $stepExecution->incrementSummaryInfo('proposal_skipped')->shouldBeCalled();

        $this->process($values)->shouldReturn(null);
    }

    public function it_ignores_the_parent_field_if_product_is_not_a_variant(
        $repository,
        $updater,
        $validator,
        $productDraftBuilder,
        $stepExecution,
        $tokenStorage,
        $mediaStorer,
        $draftSourceFactory,
        ProductInterface $product,
        ConstraintViolationListInterface $violationList,
        EntityWithValuesDraftInterface $productDraft,
        JobExecution $jobExecution,
        JobInstance $jobInstance,
        TokenInterface $token,
        UserInterface $user,
        DraftSource $draftSource,
        ExecutionContext $executionContext
    ){
        $this->prepareDraftSource($tokenStorage, $token, $user, $draftSource, $draftSourceFactory);

        $repository->findOneByIdentifier('my-sku')->willReturn($product);
        $product->isVariant()->willReturn(false);
        $productDraft->setAllReviewStatuses(EntityWithValuesDraftInterface::CHANGE_TO_REVIEW)->willReturn($productDraft);
        $values = $this->getValues();

        $mediaStorer->store($values['values'])->willReturn($values['values']);

        $updater
            ->update($product, $values)
            ->shouldBeCalled();
        $validator
            ->validate($product)
            ->willReturn($violationList);

        $productDraftBuilder->build($product, $draftSource)->willReturn($productDraft);

        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $stepExecution->getExecutionContext()->willReturn($executionContext);
        $values['parent'] = '';

        $this->process($values)->shouldReturn($productDraft);
    }

    function getValues()
    {
        return [
            'identifier' => 'my-sku',
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

    /**
     * @param $tokenStorage
     * @param TokenInterface $token
     * @param UserInterface $user
     * @param DraftSource $draftSource
     * @param PimUserDraftSourceFactory $draftSourceFactory
     */
    private function prepareDraftSource(
       TokenStorageInterface $tokenStorage,
       TokenInterface $token,
       UserInterface $user,
       DraftSource $draftSource,
       PimUserDraftSourceFactory $draftSourceFactory
    ): void {
        $fullName = 'Mary Smith';
        $username = 'mary';
        $source = 'pim';
        $sourceLabel = 'PIM';

        $user->getFullName()->willReturn($fullName);
        $user->getUsername()->willReturn($username);

        $tokenStorage->getToken()->willReturn($token);

        $token->getUsername()->willReturn($username);
        $token->getUser()->willReturn($user);

        $draftSource->getSource()->willReturn($source);
        $draftSource->getSourceLabel()->willReturn($sourceLabel);
        $draftSource->getAuthor()->willReturn($username);
        $draftSource->getAuthorLabel()->willReturn($fullName);

        $draftSourceFactory->createFromUser($user)->willReturn($draftSource);
    }
}
