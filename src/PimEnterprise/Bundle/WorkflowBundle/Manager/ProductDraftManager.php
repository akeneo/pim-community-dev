<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\Manager;

use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Pim\Bundle\CatalogBundle\Filter\CollectionFilterInterface;
use Pim\Bundle\UserBundle\Context\UserContext;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use PimEnterprise\Bundle\WorkflowBundle\Applier\ProductDraftApplierInterface;
use PimEnterprise\Bundle\WorkflowBundle\Event\ProductDraftEvents;
use PimEnterprise\Bundle\WorkflowBundle\Factory\ProductDraftFactory;
use PimEnterprise\Bundle\WorkflowBundle\Model\ProductDraftInterface;
use PimEnterprise\Bundle\WorkflowBundle\Repository\ProductDraftRepositoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Manage product product drafts
 *
 * @author Gildas Quemener <gildas@akeneo.com>
 */
class ProductDraftManager
{
    /** @var SaverInterface */
    protected $workingCopySaver;

    /** @var UserContext */
    protected $userContext;

    /** @var ProductDraftFactory */
    protected $factory;

    /** @var ProductDraftRepositoryInterface */
    protected $repository;

    /** @var ProductDraftApplierInterface */
    protected $applier;

    /** @var EventDispatcherInterface */
    protected $dispatcher;

    /** @var SaverInterface */
    protected $productDraftSaver;

    /** @var RemoverInterface */
    protected $productDraftRemover;

    /** @var CollectionFilterInterface */
    protected $valuesFilter;

    /**
     * @param SaverInterface                  $workingCopySaver
     * @param UserContext                     $userContext
     * @param ProductDraftFactory             $factory
     * @param ProductDraftRepositoryInterface $repository
     * @param ProductDraftApplierInterface    $applier
     * @param EventDispatcherInterface        $dispatcher
     * @param SaverInterface                  $productDraftSaver
     * @param RemoverInterface                $productDraftRemover
     * @param CollectionFilterInterface       $valuesFilter
     */
    public function __construct(
        SaverInterface $workingCopySaver,
        UserContext $userContext,
        ProductDraftFactory $factory,
        ProductDraftRepositoryInterface $repository,
        ProductDraftApplierInterface $applier,
        EventDispatcherInterface $dispatcher,
        SaverInterface $productDraftSaver,
        RemoverInterface $productDraftRemover,
        CollectionFilterInterface $valuesFilter
    ) {
        $this->workingCopySaver    = $workingCopySaver;
        $this->userContext         = $userContext;
        $this->factory             = $factory;
        $this->repository          = $repository;
        $this->applier             = $applier;
        $this->dispatcher          = $dispatcher;
        $this->productDraftSaver   = $productDraftSaver;
        $this->productDraftRemover = $productDraftRemover;
        $this->valuesFilter        = $valuesFilter;
    }

    /**
     * Approve a single "ready to review" change of the given $productDraft.
     * This approval is only applied if current user have edit rights on the change.
     *
     * To do that we create a temporary draft that contains the change that we want to apply,
     * then we apply this temporary draft and remove this change from the original one.
     *
     * @param ProductDraftInterface $productDraft
     * @param AttributeInterface    $attribute
     * @param LocaleInterface|null  $locale
     * @param ChannelInterface|null $channel
     * @param array                 $context ['comment' => string|null]
     *
     * @throws \LogicException If the $productDraft is not ready to be reviewed.
     */
    public function approveValue(
        ProductDraftInterface $productDraft,
        AttributeInterface $attribute,
        LocaleInterface $locale = null,
        ChannelInterface $channel = null,
        array $context = []
    ) {
        $this->dispatcher->dispatch(ProductDraftEvents::PRE_PARTIAL_APPROVE, new GenericEvent($productDraft, $context));

        if (ProductDraftInterface::READY !== $productDraft->getStatus()) {
            throw new \LogicException('A product draft not in ready state can not be partially approved');
        }

        $localeCode = null !== $locale ? $locale->getCode() : null;
        $channelCode = null !== $channel ? $channel->getCode() : null;

        $data = $productDraft->getChange($attribute->getCode(), $localeCode, $channelCode);
        $filteredValues = $this->valuesFilter->filterCollection(
            [
                $attribute->getCode() => [['locale' => $localeCode, 'scope' => $channelCode, 'data' => $data]]
            ],
            'pim.internal_api.attribute.edit'
        );

        if (!empty($filteredValues)) {
            $partialDraft = $this->createDraft($productDraft, $filteredValues);
            $this->applyDraftOnProduct($partialDraft);
            $this->removeDraftChanges($productDraft, $filteredValues);
        }

        $context['updatedValues'] = $filteredValues;

        $this->dispatcher->dispatch(
            ProductDraftEvents::POST_PARTIAL_APPROVE,
            new GenericEvent($productDraft, $context)
        );
    }

    /**
     * Approve all "ready to review" changes of the given $productDraft.
     * This approval is only applied if current user have edit rights on the change, so if
     * not all changes can be approved, a "partial approval" is done instead.
     *
     * @param ProductDraftInterface $productDraft
     * @param array                 $context ['comment' => string|null]
     *
     * @throws \LogicException If the $productDraft is not ready to be reviewed.
     */
    public function approve(ProductDraftInterface $productDraft, array $context = [])
    {
        $this->dispatcher->dispatch(ProductDraftEvents::PRE_APPROVE, new GenericEvent($productDraft, $context));

        if (ProductDraftInterface::READY !== $productDraft->getStatus()) {
            throw new \LogicException('A product draft not in ready state can not be approved');
        }

        $productDraftChanges = $productDraft->getChangesToReview();
        $filteredValues = $this->valuesFilter->filterCollection(
            $productDraftChanges['values'],
            'pim.internal_api.attribute.edit'
        );

        if (!empty($filteredValues)) {
            $fullyApproved = $filteredValues == $productDraftChanges['values'];
            $draftToApply = $fullyApproved ? $productDraft : $this->createDraft($productDraft, $filteredValues);

            $this->applyDraftOnProduct($draftToApply);
            $this->removeDraftChanges($productDraft, $filteredValues);
        }

        $context['updatedValues'] = $filteredValues;

        $this->dispatcher->dispatch(ProductDraftEvents::POST_APPROVE, new GenericEvent($productDraft, $context));
    }

    /**
     * Refuse a single "ready to review" change of the given $productDraft.
     * This refusal is only applied if current user have edit rights on the change.
     *
     * @param ProductDraftInterface $productDraft
     * @param AttributeInterface    $attribute
     * @param LocaleInterface|null  $locale
     * @param ChannelInterface|null $channel
     * @param array                 $context ['comment' => string|null]
     *
     * @throws \LogicException If the $productDraft is not ready to be reviewed.
     */
    public function refuseValue(
        ProductDraftInterface $productDraft,
        AttributeInterface $attribute,
        LocaleInterface $locale = null,
        ChannelInterface $channel = null,
        array $context = []
    ) {
        $this->dispatcher->dispatch(ProductDraftEvents::PRE_PARTIAL_REFUSE, new GenericEvent($productDraft, $context));

        if (ProductDraftInterface::READY !== $productDraft->getStatus()) {
            throw new \LogicException('A product draft not in ready state can not be partially rejected');
        }

        $localeCode = null !== $locale ? $locale->getCode() : null;
        $channelCode = null !== $channel ? $channel->getCode() : null;

        $filteredValues = $this->valuesFilter->filterCollection(
            [
                $attribute->getCode() => [['locale'  => $localeCode, 'scope' => $channelCode]]
            ],
            'pim.internal_api.attribute.edit'
        );

        if (!empty($filteredValues)) {
            $this->refuseDraftChanges($productDraft, $filteredValues);
        }

        $context['updatedValues'] = $filteredValues;

        $this->dispatcher->dispatch(
            ProductDraftEvents::POST_PARTIAL_REFUSE,
            new GenericEvent($productDraft, $context)
        );
    }

    /**
     * Refuse all "ready to review" changes of the given $productDraft.
     * This refusal is only applied if current user have edit rights on the change, so if
     * not all changes can be refused, a "partial refusal" is done instead.
     *
     * @param ProductDraftInterface $productDraft
     * @param array                 $context
     *
     * @throws \LogicException If the $productDraft is not ready to be reviewed.
     */
    public function refuse(ProductDraftInterface $productDraft, array $context = [])
    {
        $this->dispatcher->dispatch(ProductDraftEvents::PRE_REFUSE, new GenericEvent($productDraft, $context));

        if (ProductDraftInterface::READY !== $productDraft->getStatus()) {
            throw new \LogicException('A product draft not in ready state can not be rejected');
        }

        $productDraftChanges = $productDraft->getChangesToReview();
        $filteredValues = $this->valuesFilter->filterCollection(
            $productDraftChanges['values'],
            'pim.internal_api.attribute.edit'
        );

        if (!empty($filteredValues)) {
            $this->refuseDraftChanges($productDraft, $filteredValues);
        }

        $context['updatedValues'] = $filteredValues;

        $this->dispatcher->dispatch(ProductDraftEvents::POST_REFUSE, new GenericEvent($productDraft, $context));
    }

    /**
     * Remove an in progress product draft
     *
     * @param ProductDraftInterface $productDraft
     * @param array                 $context
     *
     * @throws \LogicException
     */
    public function remove(ProductDraftInterface $productDraft, array $context = [])
    {
        $this->dispatcher->dispatch(ProductDraftEvents::PRE_REMOVE, new GenericEvent($productDraft, $context));

        if (ProductDraftInterface::READY === $productDraft->getStatus()) {
            throw new \LogicException('A product draft in ready state can not be removed');
        }

        $this->productDraftRemover->remove($productDraft);

        $this->dispatcher->dispatch(ProductDraftEvents::POST_REMOVE, new GenericEvent($productDraft, $context));
    }

    /**
     * Find or create a product draft
     *
     * @param ProductInterface $product
     *
     * @throws \LogicException
     *
     * @return ProductDraftInterface
     */
    public function findOrCreate(ProductInterface $product)
    {
        if (null === $this->userContext->getUser()) {
            throw new \LogicException('Current user cannot be resolved');
        }
        $username = $this->userContext->getUser()->getUsername();
        $productDraft = $this->repository->findUserProductDraft($product, $username);

        if (null === $productDraft) {
            $productDraft = $this->factory->createProductDraft($product, $username);
        }

        return $productDraft;
    }

    /**
     * Mark a product draft as ready
     *
     * @param ProductDraftInterface $productDraft
     * @param string                $comment
     */
    public function markAsReady(ProductDraftInterface $productDraft, $comment = null)
    {
        $this->dispatcher->dispatch(ProductDraftEvents::PRE_READY, new GenericEvent($productDraft));

        $productDraft->setAllReviewStatuses(ProductDraftInterface::CHANGE_TO_REVIEW);
        $this->productDraftSaver->save($productDraft);

        $this->dispatcher->dispatch(
            ProductDraftEvents::POST_READY,
            new GenericEvent($productDraft, ['comment' => $comment])
        );
    }

    /**
     * Create a draft with the given changes.
     *
     * @param ProductDraftInterface $productDraft
     * @param array                 $draftChanges
     *
     * @return ProductDraftInterface
     */
    protected function createDraft(ProductDraftInterface $productDraft, array $draftChanges)
    {
        $partialDraft = $this->factory->createProductDraft($productDraft->getProduct(), $productDraft->getAuthor());
        $partialDraft->setChanges([
            'values' => $draftChanges
        ]);

        return $partialDraft;
    }

    /**
     * Apply a draft on a product. The product is saved.
     *
     * @param ProductDraftInterface $productDraft
     */
    protected function applyDraftOnProduct(ProductDraftInterface $productDraft)
    {
        $product = $productDraft->getProduct();
        $isPartialDraft = null === $productDraft->getId();

        if ($isPartialDraft) {
            $this->applier->applyAllChanges($product, $productDraft);
        } else {
            $this->applier->applyToReviewChanges($product, $productDraft);
        }

        $this->workingCopySaver->save($product);
    }

    /**
     * Refuse changes from a draft. The draft is saved.
     *
     * @param ProductDraftInterface $productDraft
     * @param array                 $refusedChanges
     */
    protected function refuseDraftChanges(ProductDraftInterface $productDraft, array $refusedChanges)
    {
        foreach ($refusedChanges as $attributeCode => $values) {
            foreach ($values as $value) {
                $productDraft->setReviewStatusForChange(
                    ProductDraftInterface::CHANGE_DRAFT,
                    $attributeCode,
                    $value['locale'],
                    $value['scope']
                );
            }
        }

        $this->productDraftSaver->save($productDraft);
    }

    /**
     * Remove the given changes from a draft and saves it.
     * It the draft has no more changes, it is removed.
     *
     * @param ProductDraftInterface $productDraft
     * @param array                 $appliedChanges
     */
    protected function removeDraftChanges(ProductDraftInterface $productDraft, array $appliedChanges)
    {
        foreach ($appliedChanges as $attributeCode => $values) {
            foreach ($values as $value) {
                $productDraft->removeChange($attributeCode, $value['locale'], $value['scope']);
            }
        }

        if (!$productDraft->hasChanges()) {
            $this->productDraftRemover->remove($productDraft);
        } else {
            $this->productDraftSaver->save($productDraft);
        }
    }
}
