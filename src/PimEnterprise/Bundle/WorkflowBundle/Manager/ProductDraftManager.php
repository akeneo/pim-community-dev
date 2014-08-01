<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Manager;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Doctrine\Common\Persistence\ManagerRegistry;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\UserBundle\Context\UserContext;
use PimEnterprise\Bundle\WorkflowBundle\Factory\ProductDraftFactory;
use PimEnterprise\Bundle\WorkflowBundle\Form\Applier\ProductDraftChangesApplier;
use PimEnterprise\Bundle\WorkflowBundle\Model\Proposition;
use PimEnterprise\Bundle\WorkflowBundle\Repository\ProductDraftRepositoryInterface;
use PimEnterprise\Bundle\WorkflowBundle\Event\ProductDraftEvents;
use PimEnterprise\Bundle\WorkflowBundle\Event\ProductDraftEvent;

/**
 * Manage product propositions
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class ProductDraftManager
{
    /** @var ManagerRegistry */
    protected $registry;

    /** @var ProductManager */
    protected $manager;

    /** @var UserContext */
    protected $userContext;

    /** @var ProductDraftFactory */
    protected $factory;

    /** @var ProductDraftRepositoryInterface */
    protected $repository;

    /** @var ProductDraftChangesApplier */
    protected $applier;

    /** @var EventDispatcherInterface */
    protected $dispatcher;

    /**
     * @param ManagerRegistry                $registry
     * @param ProductManager                 $manager
     * @param UserContext                    $userContext
     * @param ProductDraftFactory             $factory
     * @param ProductDraftRepositoryInterface $repository
     * @param ProductDraftChangesApplier      $applier
     * @param EventDispatcherInterface       $dispatcher
     */
    public function __construct(
        ManagerRegistry $registry,
        ProductManager $manager,
        UserContext $userContext,
        ProductDraftFactory $factory,
        ProductDraftRepositoryInterface $repository,
        ProductDraftChangesApplier $applier,
        EventDispatcherInterface $dispatcher
    ) {
        $this->registry = $registry;
        $this->manager = $manager;
        $this->userContext = $userContext;
        $this->factory = $factory;
        $this->repository = $repository;
        $this->applier = $applier;
        $this->dispatcher = $dispatcher;
    }

    /**
     * Approve a proposition
     *
     * @param Proposition $proposition
     */
    public function approve(Proposition $proposition)
    {
        $this->dispatcher->dispatch(
            ProductDraftEvents::PRE_APPROVE,
            new ProductDraftEvent($proposition)
        );

        $product = $proposition->getProduct();
        $this->applier->apply($product, $proposition);

        $manager = $this->registry->getManagerForClass(get_class($proposition));
        $manager->remove($proposition);
        $manager->flush();

        $this->manager->handleMedia($product);
        $this->manager->saveProduct($product, ['bypass_proposition' => true]);
    }

    /**
     * Refuse a proposition
     *
     * @param Proposition $proposition
     */
    public function refuse(Proposition $proposition)
    {
        $manager = $this->registry->getManagerForClass(get_class($proposition));

        if (!$proposition->isInProgress()) {
            $proposition->setStatus(Proposition::IN_PROGRESS);
        } else {
            $manager->remove($proposition);
        }

        $this->dispatcher->dispatch(
            ProductDraftEvents::PRE_REFUSE,
            new ProductDraftEvent($proposition)
        );

        $manager->flush();
    }

    /**
     * Find or create a proposition
     *
     * @param ProductInterface $product
     *
     * @return Proposition
     *
     * @throws \LogicException
     */
    public function findOrCreate(ProductInterface $product)
    {
        if (null === $this->userContext->getUser()) {
            throw new \LogicException('Current user cannot be resolved');
        }
        $username = $this->userContext->getUser()->getUsername();
        $proposition = $this->repository->findUserProposition($product, $username);

        if (null === $proposition) {
            $proposition = $this->factory->createProposition($product, $username);
        }

        return $proposition;
    }

    /**
     * Mark a proposition as ready
     *
     * @param Proposition $proposition
     */
    public function markAsReady(Proposition $proposition)
    {
        $this->dispatcher->dispatch(
            ProductDraftEvents::PRE_READY,
            new ProductDraftEvent($proposition)
        );
        $proposition->setStatus(Proposition::READY);

        $manager = $this->registry->getManagerForClass(get_class($proposition));
        $manager->flush();
    }
}
