<?php

namespace PimEnterprise\Bundle\WorkflowBundle\EventDispatcher;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Pim\Bundle\UserBundle\Context\UserContext;
use Pim\Bundle\CatalogBundle\Context\CatalogContext;
use Pim\Bundle\CatalogBundle\Model\AbstractProduct;
use Pim\Bundle\EnrichBundle\EnrichEvents;
use PimEnterprise\Bundle\WorkflowBundle\Doctrine\Repository\PropositionRepositoryInterface;
use PimEnterprise\Bundle\WorkflowBundle\Persistence\ProductChangesApplier;

/**
 * Inject current user proposition in a product before editing a product
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class InjectCurrentUserPropositionSubscriber implements EventSubscriberInterface
{
    /** @var UserContext */
    protected $userContext;

    /** @var CatalogContext */
    protected $catalogContext;

    /** @var PropositionRepositoryInterface */
    protected $repository;

    /** @var ProductChangesApplier */
    protected $applier;

    /**
     * @param UserContext                    $userContext
     * @param CatalogContext                 $catalogContext
     * @param PropositionRepositoryInterface $repository
     * @param ProductChangesApplier          $applier
     */
    public function __construct(
        UserContext $userContext,
        CatalogContext $catalogContext,
        PropositionRepositoryInterface $repository,
        ProductChangesApplier $applier
    ) {
        $this->userContext = $userContext;
        $this->catalogContext = $catalogContext;
        $this->repository = $repository;
        $this->applier = $applier;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            EnrichEvents::PRE_EDIT_PRODUCT => 'inject',
        ];
    }

    /**
     * Inject current proposition into the product
     *
     * @param GenericEvent $event
     */
    public function inject(GenericEvent $event)
    {
        $product = $event->getSubject();

        if ((null !== $user = $this->userContext->getUser())
            && (null !== $proposition = $this->getProposition(
                $product,
                $user->getUsername(),
                $this->catalogContext->getLocaleCode()
            ))) {
               $this->applier->apply($product, $proposition->getChanges());
        }
    }

    /**
     * Get a proposition
     *
     * @param AbstractProduct $product
     * @param string          $username
     * @param string          $locale
     *
     * @return Proposition|null
     */
    protected function getProposition(AbstractProduct $product, $username, $locale)
    {
        return $this->repository->findUserProposition($product, $username, $locale);
    }
}
