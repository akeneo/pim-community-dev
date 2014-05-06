<?php

namespace PimEnterprise\Bundle\CatalogBundle\Persistence;

use Symfony\Component\Security\Core\SecurityContextInterface;
use Doctrine\Common\Persistence\ManagerRegistry;
use Pim\Bundle\CatalogBundle\Persistence\ProductPersister;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Manager\CompletenessManager;
use PimEnterprise\Bundle\CatalogBundle\Factory\ProposalFactory;

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
     *
     */
    public function __construct(
        ManagerRegistry $registry,
        CompletenessManager $completenessManager,
        SecurityContextInterface $securityContext,
        ProposalFactory $factory,
        ProductChangesProvider $changesProvider
    ) {
        $this->registry = $registry;
        $this->completenessManager = $completenessManager;
        $this->securityContext = $securityContext;
        $this->factory = $factory;
        $this->changesProvider = $changesProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function persist(ProductInterface $product, array $options)
    {
        if (false /** Condition based on user right to edit the product */) {
            $this->persistProduct($product, $options);
        } else {
            $this->persistProposal($product);
        }
    }

    /**
     * Persist the product
     *
     * @param ProductInterface $product
     * @param array            $options
     */
    private function persistProduct(ProductInterface $product, array $options)
    {
        $options = array_merge(
            [
                'recalculate' => true,
                'flush' => true,
                'schedule' => true,
            ],
            $options
        );

        $manager = $this->registry->getManagerForClass(get_class($product));
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
     * @param ProductInterface $product
     */
    private function persistProposal(ProductInterface $product)
    {
        $proposal = $this->factory->createProposal(
            $product,
            $this->getUser(),
            $this->changesProvider->computeNewValues($product)
        );

        $manager = $this->registry->getManagerForClass(get_class($proposal));
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
