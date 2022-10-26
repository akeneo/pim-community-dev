<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Component\Connector\Processor\Denormalization;

use Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\Denormalizer\MediaStorer;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Applier\DraftApplierInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Builder\EntityWithValuesDraftBuilderInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Factory\PimUserDraftSourceFactory;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Repository\EntityWithValuesDraftRepositoryInterface;
use Akeneo\Tool\Component\Batch\Item\InvalidItemException;
use Akeneo\Tool\Component\Batch\Item\ItemProcessorInterface;
use Akeneo\Tool\Component\Batch\Item\NonBlockingWarningAggregatorInterface;
use Akeneo\Tool\Component\Batch\Model\Warning;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Tool\Component\Connector\Processor\Denormalization\AbstractProcessor;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Repository\CachedObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Webmozart\Assert\Assert;

/**
 * Product draft import processor, allows to,
 *  - update
 *  - validate
 *  - skip invalid ones
 *  - return the valid ones
 *
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
class ProductDraftProcessor extends AbstractProcessor implements
    ItemProcessorInterface,
    StepExecutionAwareInterface,
    NonBlockingWarningAggregatorInterface
{
    /** @var Warning[] */
    private array $nonBlockingWarnings = [];

    public function __construct(
        IdentifiableObjectRepositoryInterface $repository,
        protected ObjectUpdaterInterface $updater,
        protected ValidatorInterface $validator,
        protected EntityWithValuesDraftBuilderInterface $productDraftBuilder,
        protected DraftApplierInterface $productDraftApplier,
        protected EntityWithValuesDraftRepositoryInterface $productDraftRepo,
        protected TokenStorageInterface $tokenStorage,
        private MediaStorer $mediaStorer,
        private PimUserDraftSourceFactory $draftSourceFactory,
        private CachedObjectRepositoryInterface $attributeRepository
    ) {
        parent::__construct($repository);
    }

    /**
     * {@inheritdoc}
     */
    public function process($item)
    {
        $identifier = $this->getIdentifier($item);

        /** @var ProductInterface $product */
        $product = $this->repository->findOneByIdentifier($identifier);
        if (null === $product) {
            $this->skipItemWithMessage($item, sprintf('Product "%s" does not exist', $identifier));
        }

        $item = $this->skipReadOnlyAttributes($item);

        $product = $this->applyDraftToProduct($product);

        if (isset($item['values'])) {
            try {
                $item['values'] = $this->mediaStorer->store($item['values']);
            } catch (InvalidPropertyException $e) {
                $this->skipItemWithMessage($item, $e->getMessage(), $e);
            }
        }

        Assert::implementsInterface($product, ProductInterface::class);
        if (!$product->isVariant() && isset($item['parent']) && '' === trim($item['parent'])) {
            unset($item['parent']);
        }

        try {
            $this->updater->update($product, $item);
        } catch (\Exception $exception) {
            $this->skipItemWithMessage($item, $exception->getMessage(), $exception);
        }

        $violations = $this->validator->validate($product);
        if ($violations->count() > 0) {
            $this->skipItemWithConstraintViolations($item, $violations);
        }

        $productDraft = $this->buildDraft($product);

        if (null !== $productDraft) {
            $productDraft = $this->preventsProductDraftDuplication($productDraft, $identifier);
        }

        return $productDraft;
    }

    /**
     * Apply current draft values to product to fix problem with optional attributes
     *
     * @param EntityWithValuesInterface $entityWithValues
     *
     * @return EntityWithValuesInterface
     */
    protected function applyDraftToProduct(EntityWithValuesInterface $entityWithValues): EntityWithValuesInterface
    {
        $productDraft = $this->getProductDraft($entityWithValues);

        if (null !== $productDraft) {
            $this->productDraftApplier->applyAllChanges($entityWithValues, $productDraft);
        }

        return $entityWithValues;
    }

    protected function getProductDraft(EntityWithValuesInterface $entityWithValues): ?EntityWithValuesDraftInterface
    {
        return $this->productDraftRepo->findUserEntityWithValuesDraft($entityWithValues, $this->getUsername());
    }

    /**
     * @param array $convertedItem
     *
     * @throws \InvalidArgumentException
     *
     * @return string
     */
    protected function getIdentifier(array $convertedItem): string
    {
        if (!isset($convertedItem['identifier'])) {
            throw new \InvalidArgumentException('Column "identifier" is expected');
        }

        return $convertedItem['identifier'];
    }

    /**
     * Build product draft. If there is:
     *  - diff between product and draft: return new draft, it will be created in writer
     *  - no diff between product and draft and there is no draft for this product in DB: skip draft
     *  - no diff between product and draft and there is a draft for this product in DB: return old draft, it will be
     *      deleted in writer
     *
     * @param EntityWithValuesInterface $entityWithValues
     *
     * @throws InvalidItemException
     *
     * @return EntityWithValuesDraftInterface|null
     */
    protected function buildDraft(EntityWithValuesInterface $entityWithValues): ?EntityWithValuesDraftInterface
    {
        $productDraft = $this->productDraftBuilder->build(
            $entityWithValues,
            $this->draftSourceFactory->createFromUser($this->tokenStorage->getToken()->getUser())
        );

        // no draft has been created because there is no diff between proposal and product
        if (null === $productDraft) {
            $deprecatedDraft = $this->getProductDraft($entityWithValues);
            if (null !== $deprecatedDraft) {
                $deprecatedDraft->setChanges([]);

                return $deprecatedDraft;
            }

            $this->stepExecution->incrementSummaryInfo('proposal_skipped');

            return null;
        }

        $productDraft->setAllReviewStatuses(EntityWithValuesDraftInterface::CHANGE_TO_REVIEW);

        return $productDraft;
    }

    /**
     * @return string
     */
    protected function getUsername(): string
    {
        return $this->tokenStorage->getToken()->getUsername();
    }

    /**
     * Overwrites the previous processed product draft when there is one, to prevent duplication.
     *
     * @param EntityWithValuesDraftInterface $productDraft
     * @param string $identifier
     *
     * @return EntityWithValuesDraftInterface
     */
    private function preventsProductDraftDuplication(EntityWithValuesDraftInterface $productDraft, string $identifier): ?EntityWithValuesDraftInterface
    {
        $executionContext = $this->stepExecution->getExecutionContext();
        $processedProductDrafts = $executionContext->get('processed_items_batch') ?? [];

        if (isset($processedProductDrafts[$identifier])) {
            $processedProductDraft = $processedProductDrafts[$identifier];
            $processedProductDraft->setValues($productDraft->getValues());
            $processedProductDraft->setChanges($productDraft->getChanges());

            return null;
        }

        $processedItemsBatch[$identifier] = $productDraft;
        $executionContext->put('processed_items_batch', $processedItemsBatch);

        return $productDraft;
    }

    /**
     * Retrieve attributes in database to check is_read_only property.
     * All read only values are removed from the item['values'] and a warning message is prepared to be displayed in UI.
     * @param array $item
     * @return array
     */
    private function skipReadOnlyAttributes(array $item): array
    {
        if (!isset($item['values'])) {
            return $item;
        }

        $readOnlyAttributes = array_filter(array_keys($item['values']), function ($attributeCode) {
            $attributeFromDatabase = $this->attributeRepository->findOneByIdentifier($attributeCode);

            return $attributeFromDatabase->getProperty('is_read_only') === true;
        });
        $valuesWithoutReadOnlyAttributes = array_diff_key($item['values'], array_fill_keys($readOnlyAttributes, null));
        $item['values'] = $valuesWithoutReadOnlyAttributes;

        foreach ($readOnlyAttributes as $attributeCode) {
            $this->nonBlockingWarnings[] = new Warning(
                $this->stepExecution,
                'The field "%attribute_code%" is a read-only attribute. The product values cannot be replaced.',
                ['%attribute_code%' => $attributeCode],
                $item
            );
        }

        return $item;
    }

    public function flushNonBlockingWarnings(): array
    {
        $nonBlockingWarnings = $this->nonBlockingWarnings;
        $this->nonBlockingWarnings = [];

        return $nonBlockingWarnings;
    }
}
