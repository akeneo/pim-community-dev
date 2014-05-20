<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Persistence;

use Symfony\Component\Security\Core\SecurityContextInterface;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;
use Pim\Bundle\CatalogBundle\Persistence\ProductPersister;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Manager\CompletenessManager;
use PimEnterprise\Bundle\WorkflowBundle\Factory\ProposalFactory;

/**
 * Store product through proposals
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class ProposalPersister implements ProductPersister
{
    /** @var ManagerRegistry */
    protected $registry;

    /** @var CompletenessManager */
    protected $completenessManager;

    /** @var SecurityContextInterface */
    protected $securityContext;

    /** @var ProposalFactory */
    protected $factory;

    /** @var ProductChangesProvider */
    protected $changesProvider;

    /**
     * @param ManagerRegistry          $registry
     * @param CompletenessManager      $completenessManager
     * @param SecurityContextInterface $securityContext
     * @param ProposalFactory          $factory
     * @param ProductChangesProvider   $changesProvider
     */
    public function __construct(
        ManagerRegistry $registry,
        CompletenessManager $completenessManager,
        SecurityContextInterface $securityContext,
        ProposalFactory $factory,
        ProductChangesProvider $changesProvider
    ) {
        $this->registry            = $registry;
        $this->completenessManager = $completenessManager;
        $this->securityContext     = $securityContext;
        $this->factory             = $factory;
        $this->changesProvider     = $changesProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function persist(ProductInterface $product, array $options)
    {
        $options = array_merge(['bypass_proposal' => false], $options);

        $manager = $this->registry->getManagerForClass(get_class($product));

        if ($options['bypass_proposal'] || !$manager->contains($product)) {
            $this->persistProduct($manager, $product, $options);
        } else {
            $this->persistProposal($manager, $product);
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
     * Persist a proposal of the product
     *
     * @param ObjectManager    $manager
     * @param ProductInterface $product
     */
    private function persistProposal(ObjectManager $manager, ProductInterface $product)
    {
        $changes = $this->changesProvider->computeChanges($product);

        if (empty($changes)) {
            return;
        }

        $proposal = $this->factory->createProposal($product, $this->getUser()->getUsername(), $changes);

        $manager->persist($proposal);
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
