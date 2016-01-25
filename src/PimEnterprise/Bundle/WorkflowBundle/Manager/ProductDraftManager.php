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
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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

    /**
     * @param SaverInterface                  $workingCopySaver
     * @param UserContext                     $userContext
     * @param ProductDraftFactory             $factory
     * @param ProductDraftRepositoryInterface $repository
     * @param ProductDraftApplierInterface    $applier
     * @param EventDispatcherInterface        $dispatcher
     * @param SaverInterface                  $productDraftSaver
     * @param RemoverInterface                $productDraftRemover
     */
    public function __construct(
        SaverInterface $workingCopySaver,
        UserContext $userContext,
        ProductDraftFactory $factory,
        ProductDraftRepositoryInterface $repository,
        ProductDraftApplierInterface $applier,
        EventDispatcherInterface $dispatcher,
        SaverInterface $productDraftSaver,
        RemoverInterface $productDraftRemover
    ) {
        $this->workingCopySaver = $workingCopySaver;
        $this->userContext = $userContext;
        $this->factory = $factory;
        $this->repository = $repository;
        $this->applier = $applier;
        $this->dispatcher = $dispatcher;
        $this->productDraftSaver = $productDraftSaver;
        $this->productDraftRemover = $productDraftRemover;
    }

    /**
     * Approve partially a draft
     *
     * To do that we create a temporary draft that contain the change that we want to apply,
     * then we apply this temporary draft and remove this change from the original one.
     *
     * @param ProductDraftInterface $productDraft
     * @param AttributeInterface    $attribute
     * @param ChannelInterface|null $channel
     * @param LocaleInterface|null  $locale
     * @param array                 $context ['comment' => string|null]
     *
     * @throws \LogicException
     */
    public function partialApprove(
        ProductDraftInterface $productDraft,
        AttributeInterface $attribute,
        ChannelInterface $channel = null,
        LocaleInterface $locale = null,
        array $context = []
    ) {
        $this->dispatcher->dispatch(ProductDraftEvents::PRE_PARTIAL_APPROVE, new GenericEvent($productDraft, $context));

        $this->checkPartialActionRequirements($attribute, $channel, $locale);

        $attributeCode = $attribute->getCode();
        $localeCode    = (null !== $locale) ? $locale->getCode() : null;
        $channelCode   = (null !== $channel) ? $channel->getCode() : null;

        $data = $productDraft->getChange($attributeCode, $localeCode, $channelCode);
        if (null === $data) {
            throw new \LogicException(sprintf(
                'Change for attribute "%s" on scope "%s" and locale "%s" not found in the product.',
                $attribute->getLabel(),
                $channel->getLabel(),
                $locale->getName()
            ));
        }

        $partialDraft = $this->createPartialDraft($productDraft, $attributeCode, $localeCode, $channelCode, $data);

        $product = $productDraft->getProduct();
        $this->applier->applyAllChanges($product, $partialDraft);
        $this->workingCopySaver->save($product);

        $productDraft->removeChange($attributeCode, $localeCode, $channelCode);

        if (!$productDraft->hasChanges()) {
            $this->productDraftRemover->remove($productDraft, ['flush' => false]);
        } else {
            $this->productDraftSaver->save($productDraft);
        }

        $context['message'] = 'pimee_workflow.product_draft.notification.partial_approve';
        $context['messageParams'] = ['%attribute%' => $attribute->getLabel()];
        $context['actionType'] = 'pimee_workflow_product_draft_notification_partial_approve';

        $this->dispatcher->dispatch(ProductDraftEvents::POST_PARTIAL_APPROVE, new GenericEvent($productDraft, $context));
    }

    /**
     * Reject partially a draft
     *
     * @param ProductDraftInterface $productDraft
     * @param AttributeInterface    $attribute
     * @param ChannelInterface|null $channel
     * @param LocaleInterface|null  $locale
     * @param array                 $context
     *
     * @throws \LogicException
     */
    public function partialReject(
        ProductDraftInterface $productDraft,
        AttributeInterface $attribute,
        ChannelInterface $channel = null,
        LocaleInterface $locale = null,
        array $context = []
    ) {
        $this->dispatcher->dispatch(ProductDraftEvents::PRE_PARTIAL_REFUSE, new GenericEvent($productDraft, $context));

        $this->checkPartialActionRequirements($attribute, $channel, $locale);

        $attributeCode = $attribute->getCode();
        $localeCode    = (null !== $locale) ? $locale->getCode() : null;
        $channelCode   = (null !== $channel) ? $channel->getCode() : null;

        $productDraft->setReviewStatusForChange(
            ProductDraftInterface::CHANGE_DRAFT,
            $attributeCode,
            $localeCode,
            $channelCode
        );

        $this->productDraftSaver->save($productDraft);

        $context['message'] = 'pimee_workflow.product_draft.notification.partial_reject';
        $context['messageParams'] = ['%attribute%' => $attribute->getLabel()];
        $context['actionType'] = 'pimee_workflow_product_draft_notification_partial_reject';

        $this->dispatcher->dispatch(ProductDraftEvents::POST_PARTIAL_REFUSE, new GenericEvent($productDraft, $context));
    }

    /**
     * Approve a product draft
     *
     * @param ProductDraftInterface $productDraft
     * @param array                 $context
     *
     * @throws \LogicException
     */
    public function approve(ProductDraftInterface $productDraft, array $context = [])
    {
        $this->dispatcher->dispatch(ProductDraftEvents::PRE_APPROVE, new GenericEvent($productDraft, $context));

        if (ProductDraftInterface::READY !== $productDraft->getStatus()) {
            throw new \LogicException('A product draft not in ready state can not be approved');
        }

        $product = $productDraft->getProduct();
        $this->applier->applyToReviewChanges($product, $productDraft);
        $this->removeApprovedChanges($productDraft);

        if ($productDraft->hasChanges()) {
            $this->productDraftSaver->save($productDraft);
        } else {
            $this->productDraftRemover->remove($productDraft, ['flush' => false]);
        }

        $this->workingCopySaver->save($product);

        $this->dispatcher->dispatch(ProductDraftEvents::POST_APPROVE, new GenericEvent($productDraft, $context));
    }

    /**
     * Refuse a product draft ready for approval
     *
     * @param ProductDraftInterface $productDraft
     * @param array                 $context
     *
     * @throws \LogicException
     */
    public function refuse(ProductDraftInterface $productDraft, array $context = [])
    {
        $this->dispatcher->dispatch(ProductDraftEvents::PRE_REFUSE, new GenericEvent($productDraft, $context));

        if (ProductDraftInterface::READY !== $productDraft->getStatus()) {
            throw new \LogicException('A product draft not in ready state can not be rejected');
        }

        $productDraft->setAllReviewStatuses(ProductDraftInterface::CHANGE_DRAFT);
        $this->productDraftSaver->save($productDraft);

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

        if (ProductDraftInterface::READY !== $productDraft->getStatus()) {
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
     * @param AttributeInterface $attribute
     * @param mixed              $channel
     * @param mixed              $locale
     *
     * @throws \LogicException
     */
    protected function checkPartialActionRequirements(AttributeInterface $attribute, $channel, $locale)
    {
        if ($attribute->isScopable() && null === $channel) {
            throw new \LogicException(sprintf(
                'Trying to partially approve for the scopable attribute "%s" without scope.',
                $attribute->getCode()
            ));
        }

        if ($attribute->isLocalizable() && null === $locale) {
            throw new \LogicException(sprintf(
                'Trying to partially approve for the localizable attribute "%s" without locale.',
                $attribute->getCode()
            ));
        }
    }

    /**
     * Remove approved changes (status CHANGE_TO_REVIEW) from the product draft
     *
     * @param ProductDraftInterface $productDraft
     */
    protected function removeApprovedChanges(ProductDraftInterface $productDraft)
    {
        $changes = $productDraft->getChanges();
        foreach ($changes['review_statuses'] as $code => $reviewStatuses) {
            foreach ($reviewStatuses as $reviewStatus) {
                if (ProductDraftInterface::CHANGE_TO_REVIEW === $reviewStatus['status']){
                    $productDraft->removeChange($code, $reviewStatus['locale'], $reviewStatus['scope']);
                }
            }
        }
    }

    /**
     * @param ProductDraftInterface $productDraft
     * @param string $attributeCode
     * @param string $localeCode
     * @param string $channelCode
     * @param string $data
     *
     * @return ProductDraftInterface
     */
    protected function createPartialDraft(
        ProductDraftInterface $productDraft,
        $attributeCode,
        $localeCode,
        $channelCode,
        $data
    ) {
        $partialDraft = $this->factory->createProductDraft($productDraft->getProduct(), $productDraft->getAuthor());

        $partialDraft->setChanges([
            'values' => [
                $attributeCode => [
                    [
                        'locale' => $localeCode,
                        'scope'  => $channelCode,
                        'data'   => $data
                    ]
                ]
            ]
        ]);

        return $partialDraft;
    }
}
