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

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Permission\Component\Exception\ResourceAccessDeniedException;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Applier\DraftApplierInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Builder\EntityWithValuesDraftBuilderInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Repository\EntityWithValuesDraftRepositoryInterface;
use Akeneo\Tool\Component\Batch\Item\InvalidItemException;
use Akeneo\Tool\Component\Batch\Item\ItemProcessorInterface;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Tool\Component\Connector\Processor\Denormalization\AbstractProcessor;
use Akeneo\Tool\Component\StorageUtils\Exception\PropertyException;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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
    StepExecutionAwareInterface
{
    /** @var ObjectUpdaterInterface */
    protected $updater;

    /** @var ValidatorInterface */
    protected $validator;

    /** @var EntityWithValuesDraftBuilderInterface */
    protected $productDraftBuilder;

    /** @var DraftApplierInterface */
    protected $productDraftApplier;

    /** @var EntityWithValuesDraftRepositoryInterface */
    protected $productDraftRepo;

    /** @var TokenStorageInterface */
    protected $tokenStorage;

    public function __construct(
        IdentifiableObjectRepositoryInterface $repository,
        ObjectUpdaterInterface $updater,
        ValidatorInterface $validator,
        EntityWithValuesDraftBuilderInterface $productDraftBuilder,
        DraftApplierInterface $productDraftApplier,
        EntityWithValuesDraftRepositoryInterface $productDraftRepo,
        TokenStorageInterface $tokenStorage
    ) {
        parent::__construct($repository);

        $this->updater = $updater;
        $this->validator = $validator;
        $this->productDraftBuilder = $productDraftBuilder;
        $this->productDraftApplier = $productDraftApplier;
        $this->productDraftRepo = $productDraftRepo;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * {@inheritdoc}
     */
    public function process($item)
    {
        $identifier = $this->getIdentifier($item);

        $product = $this->repository->findOneByIdentifier($identifier);
        if (null === $product) {
            $this->skipItemWithMessage($item, sprintf('Product "%s" does not exist', $identifier));
        }

        $product = $this->applyDraftToProduct($product);

        try {
            $this->updater->update($product, $item);
        } catch (PropertyException | ResourceAccessDeniedException $exception) {
            $this->skipItemWithMessage($item, $exception->getMessage(), $exception);
        }

        $violations = $this->validator->validate($product);
        if ($violations->count() > 0) {
            $this->skipItemWithConstraintViolations($item, $violations);
        }

        return $this->buildDraft($product);
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

    /**
     * @param EntityWithValuesInterface $entityWithValues
     *
     * @return EntityWithValuesInterface|null
     */
    protected function getProductDraft(EntityWithValuesInterface $entityWithValues): ?EntityWithValuesInterface
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
        $productDraft = $this->productDraftBuilder->build($entityWithValues, $this->getUsername());

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
}
