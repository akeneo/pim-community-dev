<?php

namespace PimEnterprise\Bundle\CatalogBundle\Persistence;

use Symfony\Component\Security\Core\SecurityContextInterface;
use Doctrine\Common\Persistence\ManagerRegistry;
use Pim\Bundle\CatalogBundle\Persistence\ProductPersister;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Manager\CompletenessManager;
use PimEnterprise\Bundle\CatalogBundle\Factory\RevisionFactory;

/**
 * Store product through revisions
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class RevisionPersister implements ProductPersister
{
    /** @var ManagerRegistry */
    protected $registry;

    /** @var CompletenessManager */
    protected $completenessManager;

    /** @var SecurityContextInterface */
    protected $securityContext;

    /** @var RevisionFactory */
    protected $factory;

    /**
     * @param ManagerRegistry     $registry
     * @param CompletenessManager $completenessManager
     * @param RevisionFactory     $factory
     */
    public function __construct(
        ManagerRegistry $registry,
        CompletenessManager $completenessManager,
        SecurityContextInterface $securityContext,
        RevisionFactory $factory
    ) {
        $this->registry = $registry;
        $this->completenessManager = $completenessManager;
        $this->securityContext = $securityContext;
        $this->factory = $factory;
    }

    /**
     * {@inheritdoc}
     */
    public function persist(ProductInterface $product, array $options)
    {
        if (true /** Condition based on user right to edit the product */) {
            $this->persistProduct($product, $options);
        } else {
            $this->persistRevision($product);
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
     * Persist a revision of the product
     *
     * @param ProductInterface $product
     */
    private function persistRevision(ProductInterface $product)
    {
        $revision = $this->factory->createRevision(
            $product,
            $this->getUser(),
            $this->computeNewValues($product)
        );

        $this->registry->getManagerForClass(get_class($product))->refresh($product);

        $manager = $this->registry->getManagerForClass(get_class($revision));
        $manager->persist($revision);
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

    private function computeNewValues(ProductInterface $product)
    {
        $manager = $this->registry->getManagerForClass(get_class($product));
        $uow = $manager->getUnitOfWork();

        if ($uow instanceof \Doctrine\ORM\UnitOfWork) {
            $changeSet = $uow->getEntityChangeSet($product);
            $newValues = [];

            // $newValues only contain new values of attributes (not product property)

            return $newValues;
        } elseif (method_exists($uow, 'getDocumentChangeSet')) {
            $changeSet = $uow->getDocumentChangeSet($product);
            $newValues = [];

            // $newValues only contain new values of attributes (not product property)

            return $newValues;
        }

        throw new \LogicException('Cannot compute product new values');
    }
}
