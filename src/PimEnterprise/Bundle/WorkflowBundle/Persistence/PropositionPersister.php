<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Persistence;

use Symfony\Component\Security\Core\SecurityContextInterface;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;
use Pim\Bundle\CatalogBundle\Persistence\ProductPersister;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Manager\CompletenessManager;
use PimEnterprise\Bundle\WorkflowBundle\Factory\PropositionFactory;
use PimEnterprise\Bundle\WorkflowBundle\Form\Subscriber\CollectProductValuesSubscriber;
use PimEnterprise\Bundle\WorkflowBundle\Doctrine\Repository\PropositionRepositoryInterface;

/**
 * Store product through propositions
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class PropositionPersister implements ProductPersister
{
    /** @var ManagerRegistry */
    protected $registry;

    /** @var CompletenessManager */
    protected $completenessManager;

    /** @var SecurityContextInterface */
    protected $securityContext;

    /** @var PropositionFactory */
    protected $factory;

    /** @var CollectProductValuesSubscriber */
    protected $collector;

    /** @var PropositionRepositoryInterface */
    protected $repository;

    /**
     * @param ManagerRegistry                $registry
     * @param CompletenessManager            $completenessManager
     * @param SecurityContextInterface       $securityContext
     * @param PropositionFactory             $factory
     * @param ProductChangesProvider         $changesProvider
     * @param PropositionRepositoryInterface $repository
     */
    public function __construct(
        ManagerRegistry $registry,
        CompletenessManager $completenessManager,
        SecurityContextInterface $securityContext,
        PropositionFactory $factory,
        CollectProductValuesSubscriber $collector,
        PropositionRepositoryInterface $repository
    ) {
        $this->registry = $registry;
        $this->completenessManager = $completenessManager;
        $this->securityContext = $securityContext;
        $this->factory = $factory;
        $this->collector = $collector;
        $this->repository = $repository;
    }

    /**
     * {@inheritdoc}
     */
    public function persist(ProductInterface $product, array $options)
    {
        $options = array_merge(['bypass_proposition' => false], $options);

        $manager = $this->registry->getManagerForClass(get_class($product));

        if ($options['bypass_proposition'] || !$manager->contains($product)) {
            $this->persistProduct($manager, $product, $options);
        } else {
            $this->persistProposition($manager, $product);
        }
    }

    /**
     * Persist the product
     *
     * @param ObjectManager    $manager
     * @param ProductInterface $product
     * @param array            $options
     */
    private function persistProduct(ObjectManager $manager, ProductInterface $product, array $options)
    {
        $options = array_merge(
            [
                'recalculate' => true,
                'flush' => true,
                'schedule' => true,
            ],
            $options
        );

        $manager->persist($product);

        if ($options['schedule'] || $options['recalculate']) {
            $this->completenessManager->schedule($product);
        }

        if ($options['recalculate'] || $options['flush']) {
            $manager->flush();
        }

        if ($options['recalculate']) {
            $this->completenessManager->generateMissingForProduct($product);
        }
    }

    /**
     * Persist a proposition of the product
     *
     * @param ObjectManager    $manager
     * @param ProductInterface $product
     */
    private function persistProposition(ObjectManager $manager, ProductInterface $product)
    {
        $changes = $this->collector->getChanges();
        $username = $this->getUser()->getUsername();
        $locale = $product->getLocale();
        $proposition = $this->repository->findUserProposition($username, $locale);

        if (empty($changes)) {
            if (null !== $proposition) {
                $manager->remove($proposition);
            }

            return $manager->flush();
        }

        if (null === $proposition) {
            $proposition = $this->factory->createProposition($product, $username, $locale);
            $manager->persist($proposition);
        }

        $proposition->setChanges($changes);

        $manager->flush();
    }

    /**
     * Get user from the security context
     *
     * @return \Symfony\Component\Security\Core\User\UserInterface
     *
     * @throws LogicException
     */
    private function getUser()
    {
        if (null === $token = $this->securityContext->getToken()) {
            throw new \LogicException('No user logged in');
        }

        if (!is_object($user = $token->getUser())) {
            throw new \LogicException('No user logged in');
        }

        return $user;
    }
}
