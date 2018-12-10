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
use Akeneo\Pim\Enrichment\Component\Product\ProductModel\Filter\AttributeFilterInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Applier\DraftApplierInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Builder\EntityWithValuesDraftBuilderInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Repository\EntityWithValuesDraftRepositoryInterface;
use Akeneo\Tool\Component\Batch\Item\InvalidItemException;
use Akeneo\Tool\Component\Batch\Item\ItemProcessorInterface;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Tool\Component\Connector\Processor\Denormalization\AbstractProcessor;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Product model draft import processor, allows to,
 *  - update
 *  - validate
 *  - skip invalid ones
 *  - return the valid ones
 *
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
class ProductModelDraftProcessor extends AbstractProcessor implements
    ItemProcessorInterface,
    StepExecutionAwareInterface
{
    /** @var ObjectUpdaterInterface */
    private $updater;

    /** @var ValidatorInterface */
    private $validator;

    /** @var EntityWithValuesDraftBuilderInterface */
    private $productModelDraftBuilder;

    /** @var DraftApplierInterface */
    private $productModelDraftApplier;

    /** @var EntityWithValuesDraftRepositoryInterface */
    private $productDraftRepo;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var AttributeFilterInterface */
    private $productModelAttributeFilter;

    public function __construct(
        IdentifiableObjectRepositoryInterface $repository,
        ObjectUpdaterInterface $updater,
        ValidatorInterface $validator,
        EntityWithValuesDraftBuilderInterface $productDraftBuilder,
        DraftApplierInterface $productDraftApplier,
        EntityWithValuesDraftRepositoryInterface $productDraftRepo,
        TokenStorageInterface $tokenStorage,
        AttributeFilterInterface $productModelAttributeFilter
    ) {
        parent::__construct($repository);

        $this->updater = $updater;
        $this->validator = $validator;
        $this->productModelDraftBuilder = $productDraftBuilder;
        $this->productModelDraftApplier = $productDraftApplier;
        $this->productDraftRepo = $productDraftRepo;
        $this->tokenStorage = $tokenStorage;
        $this->productModelAttributeFilter = $productModelAttributeFilter;
    }

    /**
     * {@inheritdoc}
     */
    public function process($item)
    {
        $identifier = $this->getIdentifier($item);

        $productModel = $this->repository->findOneByIdentifier($identifier);
        if (null === $productModel) {
            $this->skipItemWithMessage($item, sprintf('Product model "%s" does not exist', $identifier));
        }

        $productModel = $this->applyDraftToProductModel($productModel);

        try {
            $item = $this->productModelAttributeFilter->filter($item);
            $this->updater->update($productModel, $item);
        } catch (\Exception $exception) {
            $this->skipItemWithMessage($item, $exception->getMessage(), $exception);
        }

        $violations = $this->validator->validate($productModel);
        if ($violations->count() > 0) {
            $this->skipItemWithConstraintViolations($item, $violations);
        }

        return $this->buildDraft($productModel);
    }

    /**
     * Apply current draft values to product model
     */
    private function applyDraftToProductModel(EntityWithValuesInterface $productModel): EntityWithValuesInterface
    {
        $productModelDraft = $this->getProductModelDraft($productModel);

        if (null !== $productModelDraft) {
            $this->productModelDraftApplier->applyAllChanges($productModel, $productModelDraft);
        }

        return $productModel;
    }

    private function getProductModelDraft(EntityWithValuesInterface $productModel): ?EntityWithValuesInterface
    {
        return $this->productDraftRepo->findUserEntityWithValuesDraft($productModel, $this->getUsername());
    }

    /**
     * @throws \InvalidArgumentException
     */
    private function getIdentifier(array $convertedItem): string
    {
        if (!isset($convertedItem['code'])) {
            throw new \InvalidArgumentException('Column "code" is expected');
        }

        return $convertedItem['code'];
    }

    /**
     * Build product draft. If there is:
     *  - diff between product and draft: return new draft, it will be created in writer
     *  - no diff between product and draft and there is no draft for this product in DB: skip draft
     *  - no diff between product and draft and there is a draft for this product in DB: return old draft, it will be
     *      deleted in writer
     *
     * @throws InvalidItemException
     */
    private function buildDraft(EntityWithValuesInterface $productModel): ?EntityWithValuesDraftInterface
    {
        $productModelDraft = $this->productModelDraftBuilder->build($productModel, $this->getUsername());

        // no draft has been created because there is no diff between proposal and product
        if (null === $productModelDraft) {
            $deprecatedDraft = $this->getProductModelDraft($productModel);
            if (null !== $deprecatedDraft) {
                $deprecatedDraft->setChanges([]);

                return $deprecatedDraft;
            }

            $this->stepExecution->incrementSummaryInfo('proposal_skipped');

            return null;
        }

        $productModelDraft->setAllReviewStatuses(EntityWithValuesDraftInterface::CHANGE_TO_REVIEW);

        return $productModelDraft;
    }

    private function getUsername(): string
    {
        return $this->tokenStorage->getToken()->getUsername();
    }
}
