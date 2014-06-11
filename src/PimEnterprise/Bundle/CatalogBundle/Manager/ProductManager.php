<?php

namespace PimEnterprise\Bundle\CatalogBundle\Manager;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Pim\Bundle\CatalogBundle\Builder\ProductBuilder;
use Pim\Bundle\CatalogBundle\Entity\Repository\AssociationTypeRepository;
use Pim\Bundle\CatalogBundle\Entity\Repository\AttributeOptionRepository;
use Pim\Bundle\CatalogBundle\Entity\Repository\AttributeRepository;
use Pim\Bundle\CatalogBundle\Manager\MediaManager;
use Pim\Bundle\CatalogBundle\Manager\ProductManager as BaseProductManager;
use Pim\Bundle\CatalogBundle\Persistence\ProductPersister;
use Pim\Bundle\CatalogBundle\Repository\ProductRepositoryInterface;
use Pim\Bundle\UserBundle\Context\UserContext;
use PimEnterprise\Bundle\WorkflowBundle\Doctrine\Repository\PropositionRepositoryInterface;
use PimEnterprise\Bundle\WorkflowBundle\Persistence\ProductChangesApplier;

/**
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class ProductManager extends BaseProductManager
{
    /**
     * {@inheritdoc}
     */
    public function __construct(
        $configuration,
        ObjectManager $objectManager,
        ProductPersister $persister,
        EventDispatcherInterface $eventDispatcher,
        MediaManager $mediaManager,
        ProductBuilder $builder,
        ProductRepositoryInterface $productRepository,
        AssociationTypeRepository $assocTypeRepository,
        AttributeRepository $attributeRepository,
        AttributeOptionRepository $attOptionRepository,
        UserContext $userContext,
        PropositionRepositoryInterface $propositionRepository,
        ProductChangesApplier $applier
    ) {
        parent::__construct(
            $configuration,
            $objectManager,
            $persister,
            $eventDispatcher,
            $mediaManager,
            $builder,
            $productRepository,
            $assocTypeRepository,
            $attributeRepository,
            $attOptionRepository
        );
        $this->userContext = $userContext;
        $this->propositionRepository = $propositionRepository;
        $this->applier = $applier;
    }

    /**
     * {@inheritdoc}
     */
    public function find($id)
    {
        $product = parent::find($id);
        if ((null !== $product)
            && (null !== $user = $this->userContext->getUser())
            && (null !== $proposition = $this->getProposition($user->getUsername(), 'en_US'))) {
                $this->applier->apply($product, $proposition->getChanges());
        }

        return $product;
    }

    protected function getProposition($username, $locale)
    {
        return $this->propositionRepository->findUserProposition($username, $locale);
    }
}
