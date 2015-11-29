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
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Bundle\UserBundle\Context\UserContext;
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
        $change = $productDraft->getChangeForAttribute($attribute, $channel, $locale);

        if (null === $change) {
            throw new \LogicException(sprintf(
                'Change for attribute "%s" on scope "%s" and locale "%s" not found in the product.',
                $attribute->getLabel(),
                $channel->getLabel(),
                $locale->getName()
            ));
        }

        $temporaryDraft = $this->factory->createProductDraft($productDraft->getProduct(), $productDraft->getAuthor());

        $temporaryDraft->setChanges(['values' => [$attribute->getCode() => [[
            'locale' => null !== $locale ? $locale->getCode() : null,
            'scope'  => null !== $channel ? $channel->getCode() : null,
            'data'   => $change
        ]]]]);

        $context['message'] = 'pimee_workflow.product_draft.notification.partial_approve';
        $context['messageParams'] = ['%attribute%' => $attribute->getLabel()];
        $context['actionType'] = 'pimee_workflow_product_draft_notification_partial_approve';

        $this->approve($temporaryDraft, $context);

        $productDraft->removeChangeForAttribute($attribute, $channel, $locale);

        if (!$productDraft->hasChanges()) {
            $this->productDraftRemover->remove($productDraft, ['flush' => false]);
        } else {
            $this->productDraftSaver->save($productDraft);
        }
    }

    /**
     * Approve a product draft
     *
     * @param ProductDraftInterface $productDraft
     * @param array                 $context
     */
    public function approve(ProductDraftInterface $productDraft, array $context = [])
    {
        $this->dispatcher->dispatch(ProductDraftEvents::PRE_APPROVE, new GenericEvent($productDraft, $context));

        $product = $productDraft->getProduct();
        $this->applier->apply($product, $productDraft);

        if (null !== $productDraft->getId()) {
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
     */
    public function refuse(ProductDraftInterface $productDraft, array $context = [])
    {
        $this->dispatcher->dispatch(ProductDraftEvents::PRE_REFUSE, new GenericEvent($productDraft, $context));

        if (!$productDraft->isInProgress()) {
            $productDraft->setStatus(ProductDraftInterface::IN_PROGRESS);
            $this->productDraftSaver->save($productDraft);
        }

        $this->dispatcher->dispatch(ProductDraftEvents::POST_REFUSE, new GenericEvent($productDraft, $context));
    }

    /**
     * Remove an in progress product draft
     *
     * @param ProductDraftInterface $productDraft
     * @param array                 $context
     */
    public function remove(ProductDraftInterface $productDraft, array $context = [])
    {
        $this->dispatcher->dispatch(ProductDraftEvents::PRE_REMOVE, new GenericEvent($productDraft, $context));

        if ($productDraft->isInProgress()) {
            $this->productDraftRemover->remove($productDraft);
        }

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
        $productDraft->setStatus(ProductDraftInterface::READY);

        $this->productDraftSaver->save($productDraft);

        $this->dispatcher->dispatch(
            ProductDraftEvents::POST_READY,
            new GenericEvent($productDraft, ['comment' => $comment])
        );
    }
}
