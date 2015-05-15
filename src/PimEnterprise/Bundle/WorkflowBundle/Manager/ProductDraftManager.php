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

use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Doctrine\Common\Persistence\ManagerRegistry;
use Pim\Bundle\CatalogBundle\Manager\MediaManager;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\UserBundle\Context\UserContext;
use PimEnterprise\Bundle\WorkflowBundle\Applier\ApplierInterface;
use PimEnterprise\Bundle\WorkflowBundle\Event\ProductDraftEvents;
use PimEnterprise\Bundle\WorkflowBundle\Factory\ProductDraftFactory;
use PimEnterprise\Bundle\WorkflowBundle\Model\ProductDraft;
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
    /** @var ManagerRegistry */
    protected $registry;

    /** @var SaverInterface */
    protected $workingCopySaver;

    /** @var UserContext */
    protected $userContext;

    /** @var ProductDraftFactory */
    protected $factory;

    /** @var ProductDraftRepositoryInterface */
    protected $repository;

    /** @var ApplierInterface */
    protected $applier;

    /** @var EventDispatcherInterface */
    protected $dispatcher;

    /**
     * @param ManagerRegistry                 $registry
     * @param SaverInterface                  $workingCopySaver
     * @param UserContext                     $userContext
     * @param ProductDraftFactory             $factory
     * @param ProductDraftRepositoryInterface $repository
     * @param ApplierInterface                $applier
     * @param EventDispatcherInterface        $dispatcher
     * @param MediaManager                    $mediaManager
     */
    public function __construct(
        ManagerRegistry $registry,
        SaverInterface $workingCopySaver,
        UserContext $userContext,
        ProductDraftFactory $factory,
        ProductDraftRepositoryInterface $repository,
        ApplierInterface $applier,
        EventDispatcherInterface $dispatcher,
        MediaManager $mediaManager
    ) {
        $this->registry = $registry;
        $this->workingCopySaver = $workingCopySaver;
        $this->userContext = $userContext;
        $this->factory = $factory;
        $this->repository = $repository;
        $this->applier = $applier;
        $this->dispatcher = $dispatcher;
        $this->mediaManager = $mediaManager;
    }

    /**
     * Approve a product draft
     *
     * @param ProductDraft $productDraft
     */
    public function approve(ProductDraft $productDraft)
    {
        $this->dispatcher->dispatch(ProductDraftEvents::PRE_APPROVE, new GenericEvent($productDraft));

        $product = $productDraft->getProduct();
        $this->applier->apply($product, $productDraft);
        $this->mediaManager->handleProductMedias($product);

        $objectManager = $this->registry->getManagerForClass(get_class($productDraft));
        $objectManager->remove($productDraft);
        $objectManager->flush();

        $this->workingCopySaver->save($product);

        $this->dispatcher->dispatch(ProductDraftEvents::POST_APPROVE, new GenericEvent($productDraft));
    }

    /**
     * Refuse a product draft
     *
     * @param ProductDraft $productDraft
     */
    public function refuse(ProductDraft $productDraft)
    {
        $this->dispatcher->dispatch(ProductDraftEvents::PRE_REFUSE, new GenericEvent($productDraft));

        $objectManager = $this->registry->getManagerForClass(get_class($productDraft));

        if (!$productDraft->isInProgress()) {
            $productDraft->setStatus(ProductDraft::IN_PROGRESS);
        } else {
            $objectManager->remove($productDraft);
        }

        $objectManager->flush();

        $this->dispatcher->dispatch(ProductDraftEvents::POST_REFUSE, new GenericEvent($productDraft));
    }

    /**
     * Find or create a product draft
     *
     * @param ProductInterface $product
     *
     * @throws \LogicException
     *
     * @return ProductDraft
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
     * @param ProductDraft $productDraft
     */
    public function markAsReady(ProductDraft $productDraft)
    {
        $this->dispatcher->dispatch(ProductDraftEvents::PRE_READY, new GenericEvent($productDraft));
        $productDraft->setStatus(ProductDraft::READY);

        $manager = $this->registry->getManagerForClass(get_class($productDraft));
        $manager->flush();

        $this->dispatcher->dispatch(ProductDraftEvents::POST_READY, new GenericEvent($productDraft));
    }
}
