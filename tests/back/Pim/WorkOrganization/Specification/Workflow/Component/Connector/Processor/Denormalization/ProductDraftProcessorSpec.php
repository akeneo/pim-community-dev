<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Component\Connector\Processor\Denormalization;

use Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\Denormalizer\MediaStorer;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
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
use Akeneo\Tool\Component\Batch\Model\Warning;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\CachedObjectRepositoryInterface;
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
        PimUserDraftSourceFactory $draftSourceFactory,
        CachedObjectRepositoryInterface $attributeRepository
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
            $draftSourceFactory,
            $attributeRepository
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
        $attributeRepository,
        ProductInterface $product,
        ConstraintViolationListInterface $violationList,
        EntityWithValuesDraftInterface $productDraft,
        JobExecution $jobExecution,
        JobInstance $jobInstance,
        TokenInterface $token,
        UserInterface $user,
        DraftSource $draftSource,
        ExecutionContext $executionContext,
        AttributeInterface $attribute
    ) {
        $this->prepareDraftSource($tokenStorage, $token, $user, $draftSource, $draftSourceFactory);

        $repository->findOneByIdentifier('my-sku')->willReturn($product);
        $product->isVariant()->willReturn(false);

        $productDraft->setAllReviewStatuses(EntityWithValuesDraftInterface::CHANGE_TO_REVIEW)->willReturn($productDraft);

        $productValues = $this->getProductValues($this->getValues());

        $attribute->getProperty('is_read_only')->willReturn(false);
        $attributeRepository->findOneByIdentifier('sku')->willReturn($attribute);
        $attributeRepository->findOneByIdentifier('main_color')->willReturn($attribute);
        $attributeRepository->findOneByIdentifier('description')->willReturn($attribute);
        $attributeRepository->findOneByIdentifier('release_date')->willReturn($attribute);
        $attributeRepository->findOneByIdentifier('price')->willReturn($attribute);

        $mediaStorer->store($productValues['values'])->willReturn($productValues['values']);

        $updater
            ->update($product, $productValues)
            ->shouldBeCalled();

        $validator
            ->validate($product)
            ->willReturn($violationList);

        $productDraftBuilder->build($product, $draftSource)->willReturn($productDraft);

        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $stepExecution->getExecutionContext()->willReturn($executionContext);

        $this
            ->process($productValues)
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
        $attributeRepository,
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
        WriteValueCollection $draftValues,
        AttributeInterface $attribute
    )
    {
        $this->prepareDraftSource($tokenStorage, $token, $user, $draftSource, $draftSourceFactory);

        $repository->findOneByIdentifier('my-sku')->willReturn($product);
        $product->isVariant()->willReturn(false);
        $productDraft->setAllReviewStatuses(EntityWithValuesDraftInterface::CHANGE_TO_REVIEW)->willReturn($productDraft);

        $productValues = $this->getProductValues($this->getValues());

        $attribute->getProperty('is_read_only')->willReturn(null);
        $attributeRepository->findOneByIdentifier('sku')->willReturn($attribute);
        $attributeRepository->findOneByIdentifier('main_color')->willReturn($attribute);
        $attributeRepository->findOneByIdentifier('description')->willReturn($attribute);
        $attributeRepository->findOneByIdentifier('release_date')->willReturn($attribute);
        $attributeRepository->findOneByIdentifier('price')->willReturn($attribute);

        $mediaStorer->store($productValues['values'])->willReturn($productValues['values']);

        $updater
            ->update($product, $productValues)
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

        $productDraft->getChanges()->willReturn($productValues);
        $previousProductDraft->setChanges($productValues)->shouldBeCalled();

        $this
            ->process($productValues)
            ->shouldReturn(null);
    }

    function it_skips_a_proposal_if_there_is_no_identifier()
    {
        $productValues = $this->getProductValues($this->getValues());

        unset($productValues['identifier']);

        $this
            ->shouldThrow(new \InvalidArgumentException('Column "identifier" is expected'))
            ->during(
                'process',
                [$productValues]
            );
    }

    function it_skips_a_proposal_if_there_is_a_read_only_attribute(
        $repository,
        $updater,
        $validator,
        $productDraftBuilder,
        $stepExecution,
        $tokenStorage,
        $mediaStorer,
        $draftSourceFactory,
        $attributeRepository,
        ProductInterface $product,
        ConstraintViolationListInterface $violationList,
        EntityWithValuesDraftInterface $productDraft,
        JobExecution $jobExecution,
        JobInstance $jobInstance,
        TokenInterface $token,
        UserInterface $user,
        DraftSource $draftSource,
        ExecutionContext $executionContext,
        AttributeInterface $attributeReadOnly,
        AttributeInterface $attributeNotReadOnly,
    ) {
        $this->prepareDraftSource($tokenStorage, $token, $user, $draftSource, $draftSourceFactory);

        $repository->findOneByIdentifier('my-sku')->willReturn($product);
        $product->isVariant()->willReturn(false);

        $productDraft->setAllReviewStatuses(EntityWithValuesDraftInterface::CHANGE_TO_REVIEW)->willReturn($productDraft);

        $values = $this->getValues();
        $productValues = $this->getProductValues($values);

        $attributeReadOnly->getProperty('is_read_only')->willReturn(true);
        $attributeNotReadOnly->getProperty('is_read_only')->willReturn(false);
        $attributeRepository->findOneByIdentifier('sku')->willReturn($attributeReadOnly);
        $attributeRepository->findOneByIdentifier('main_color')->willReturn($attributeNotReadOnly);
        $attributeRepository->findOneByIdentifier('description')->willReturn($attributeNotReadOnly);
        $attributeRepository->findOneByIdentifier('release_date')->willReturn($attributeNotReadOnly);
        $attributeRepository->findOneByIdentifier('price')->willReturn($attributeNotReadOnly);

        $valuesWithoutReadOnlyAttributes = array_diff_key($values, ['sku' => null]);
        $mediaStorer->store($valuesWithoutReadOnlyAttributes)->willReturn($valuesWithoutReadOnlyAttributes);

        $productValuesWithoutReadOnlyAttribute = $this->getProductValues($valuesWithoutReadOnlyAttributes);

        $updater
            ->update($product, $productValuesWithoutReadOnlyAttribute)
            ->shouldBeCalled();

        $validator
            ->validate($product)
            ->willReturn($violationList);

        $productDraftBuilder->build($product, $draftSource)->willReturn($productDraft);

        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $stepExecution->getExecutionContext()->willReturn($executionContext);

        $this
            ->process($productValues)
            ->shouldReturn($productDraft);

        $nonBlockingwarnings = $this->flushNonBlockingWarnings();
        $nonBlockingwarnings->shouldHaveCount(1);
        $nonBlockingwarnings[0]->shouldBeAnInstanceOf(Warning::class);
    }

    function it_skips_a_proposal_if_product_does_not_exist(
        $repository,
        $stepExecution
    ) {
        $repository->findOneByIdentifier('my-sku')->willReturn(null);

        $productValues = $this->getProductValues($this->getValues());

        $stepExecution->incrementSummaryInfo('skip')->shouldBeCalled();

        $this->setStepExecution($stepExecution);
        $stepExecution->getSummaryInfo('item_position')->willReturn(1);
        $stepExecution->incrementSummaryInfo('skip')->shouldBeCalled();

        $this->shouldThrow(InvalidItemException::class)
            ->during(
                'process',
                [$productValues]
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
        $attributeRepository,
        ProductInterface $product,
        ConstraintViolationListInterface $violationList,
        JobExecution $jobExecution,
        TokenInterface $token,
        UserInterface $user,
        DraftSource $draftSource,
        AttributeInterface $attribute
    ) {
        $this->prepareDraftSource($tokenStorage, $token, $user, $draftSource, $draftSourceFactory);

        $repository->findOneByIdentifier('my-sku')->willReturn($product);
        $product->isVariant()->willReturn(false);

        $productValues = $this->getProductValues($this->getValues());

        $attribute->getProperty('is_read_only')->willReturn(null);
        $attributeRepository->findOneByIdentifier('sku')->willReturn($attribute);
        $attributeRepository->findOneByIdentifier('main_color')->willReturn($attribute);
        $attributeRepository->findOneByIdentifier('description')->willReturn($attribute);
        $attributeRepository->findOneByIdentifier('release_date')->willReturn($attribute);
        $attributeRepository->findOneByIdentifier('price')->willReturn($attribute);

        $mediaStorer->store($productValues['values'])->willReturn($productValues['values']);

        $updater
            ->update($product, $productValues)
            ->shouldBeCalled();

        $validator
            ->validate($product)
            ->willReturn($violationList);

        $productDraftBuilder->build($product, $draftSource)->willReturn(null);

        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $stepExecution->incrementSummaryInfo('proposal_skipped')->shouldBeCalled();

        $this->process($productValues)->shouldReturn(null);
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
        $attributeRepository,
        ProductInterface $product,
        ConstraintViolationListInterface $violationList,
        EntityWithValuesDraftInterface $productDraft,
        JobExecution $jobExecution,
        JobInstance $jobInstance,
        TokenInterface $token,
        UserInterface $user,
        DraftSource $draftSource,
        ExecutionContext $executionContext,
        AttributeInterface $attribute
    ){
        $this->prepareDraftSource($tokenStorage, $token, $user, $draftSource, $draftSourceFactory);

        $repository->findOneByIdentifier('my-sku')->willReturn($product);
        $product->isVariant()->willReturn(false);
        $productDraft->setAllReviewStatuses(EntityWithValuesDraftInterface::CHANGE_TO_REVIEW)->willReturn($productDraft);

        $productValues = $this->getProductValues($this->getValues());

        $attribute->getProperty('is_read_only')->willReturn(null);
        $attributeRepository->findOneByIdentifier('sku')->willReturn($attribute);
        $attributeRepository->findOneByIdentifier('main_color')->willReturn($attribute);
        $attributeRepository->findOneByIdentifier('description')->willReturn($attribute);
        $attributeRepository->findOneByIdentifier('release_date')->willReturn($attribute);
        $attributeRepository->findOneByIdentifier('price')->willReturn($attribute);

        $mediaStorer->store($productValues['values'])->willReturn($productValues['values']);

        $updater
            ->update($product, $productValues)
            ->shouldBeCalled();
        $validator
            ->validate($product)
            ->willReturn($violationList);

        $productDraftBuilder->build($product, $draftSource)->willReturn($productDraft);

        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $stepExecution->getExecutionContext()->willReturn($executionContext);
        $productValues['parent'] = '';

        $this->process($productValues)->shouldReturn($productDraft);
    }

    private function getProductValues(array $values): array
    {
        return [
            'identifier' => 'my-sku',
            'values' => $values
        ];
    }

    private function getValues(): array
    {
        return [
            'sku' => [
                [
                    'locale' => null,
                    'scope' => null,
                    'data' => 'my-sku'
                ]
            ],
            'main_color' => [
                [
                    'locale' => null,
                    'scope' => null,
                    'data' => 'white'
                ]
            ],
            'description' => [
                [
                    'locale' => 'fr_FR',
                    'scope' => 'ecommerce',
                    'data' => '<p>description</p>'
                ],
                [
                    'locale' => 'en_US',
                    'scope' => 'ecommerce',
                    'data' => '<p>description</p>'
                ],
            ],
            'release_date' => [
                [
                    'locale' => null,
                    'scope' => null,
                    'data' => '1977-08-19'
                ]
            ],
            'price' => [
                [
                    'locale' => null,
                    'scope' => null,
                    'data' => [
                        [
                            'currency' => 'EUR',
                            'data' => '10.25'
                        ],
                        [
                            'currency' => 'USD',
                            'data' => '11.5'
                        ],
                    ]
                ]
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
       PimUserDraftSourceFactory $draftSourceFactory,
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
