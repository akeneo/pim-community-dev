<?php

namespace PimEnterprise\Bundle\WorkflowBundle\EventSubscriber\Enrich;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Pim\Bundle\UserBundle\Context\UserContext;
use Pim\Bundle\CatalogBundle\Context\CatalogContext;
use Pim\Bundle\CatalogBundle\Model\AbstractProduct;
use Pim\Bundle\EnrichBundle\Event\ProductEvents;
use PimEnterprise\Bundle\WorkflowBundle\Repository\ProductDraftRepositoryInterface;
use PimEnterprise\Bundle\WorkflowBundle\Form\Applier\ProductDraftChangesApplier;
use Symfony\Component\Validator\Exception\ValidatorException;

/**
 * Inject current user proposition in a product before editing a product
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class InjectCurrentUserProductDraftSubscriber implements EventSubscriberInterface
{
    /** @var UserContext */
    protected $userContext;

    /** @var CatalogContext */
    protected $catalogContext;

    /** @var ProductDraftRepositoryInterface */
    protected $repository;

    /** @var ProductDraftChangesApplier */
    protected $applier;

    /**
     * @param UserContext                    $userContext
     * @param CatalogContext                 $catalogContext
     * @param ProductDraftRepositoryInterface $repository
     * @param ProductDraftChangesApplier      $applier
     */
    public function __construct(
        UserContext $userContext,
        CatalogContext $catalogContext,
        ProductDraftRepositoryInterface $repository,
        ProductDraftChangesApplier $applier
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
            ProductEvents::PRE_EDIT => 'inject',
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
            && (null !== $productDraft = $this->getProposition(
                $product,
                $user->getUsername(),
                $this->catalogContext->getLocaleCode()
            ))) {
            try {
                $this->applier->apply($product, $productDraft);
            } catch (ValidatorException $e) {
                // Do nothing here at the moment
                //TODO: remove this try catch and load the form directly with the potential errors
            }
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
