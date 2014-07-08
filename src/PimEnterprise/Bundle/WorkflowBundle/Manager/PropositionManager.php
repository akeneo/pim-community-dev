<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Manager;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Doctrine\Common\Persistence\ManagerRegistry;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\UserBundle\Context\UserContext;
use PimEnterprise\Bundle\WorkflowBundle\Factory\PropositionFactory;
use PimEnterprise\Bundle\WorkflowBundle\Form\Applier\PropositionChangesApplier;
use PimEnterprise\Bundle\WorkflowBundle\Model\Proposition;
use PimEnterprise\Bundle\WorkflowBundle\Repository\PropositionRepositoryInterface;
use PimEnterprise\Bundle\WorkflowBundle\Event\PropositionEvents;
use PimEnterprise\Bundle\WorkflowBundle\Event\PropositionEvent;

/**
 * Manage product propositions
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class PropositionManager
{
    /** @var ManagerRegistry */
    protected $registry;

    /** @var ProductManager */
    protected $manager;

    /** @var UserContext */
    protected $userContext;

    /** @var PropositionFactory */
    protected $factory;

    /** @var PropositionRepositoryInterface */
    protected $repository;

    /** @var PropositionChangesApplier */
    protected $applier;

    /** @var EventDispatcherInterface */
    protected $dispatcher;

    /**
     * @param ManagerRegistry                $registry
     * @param ProductManager                 $manager
     * @param UserContext                    $userContext
     * @param PropositionFactory             $factory
     * @param PropositionRepositoryInterface $repository
     * @param PropositionChangesApplier      $applier
     * @param EventDispatcherInterface       $dispatcher
     */
    public function __construct(
        ManagerRegistry $registry,
        ProductManager $manager,
        UserContext $userContext,
        PropositionFactory $factory,
        PropositionRepositoryInterface $repository,
        PropositionChangesApplier $applier,
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
        $product = $proposition->getProduct();
        $this->applier->apply($product, $proposition);

        $this->dispatcher->dispatch(
            PropositionEvents::PRE_APPROVE,
            new PropositionEvent($proposition)
        );


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
            PropositionEvents::PRE_REFUSE,
            new PropositionEvent($proposition)
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
     *
     * TODO (2014-06-18 17:05 by Gildas): Use this method in the PropositionPersister
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
            PropositionEvents::PRE_READY,
            new PropositionEvent($proposition)
        );
        $proposition->setStatus(Proposition::READY);

        $manager = $this->registry->getManagerForClass(get_class($proposition));
        $manager->flush();
    }
}
