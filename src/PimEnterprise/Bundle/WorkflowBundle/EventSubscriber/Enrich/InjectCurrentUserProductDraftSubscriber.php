<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\EventSubscriber\Enrich;

use Pim\Bundle\CatalogBundle\Context\CatalogContext;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\EnrichBundle\Event\ProductEvents;
use Pim\Bundle\UserBundle\Context\UserContext;
use PimEnterprise\Bundle\WorkflowBundle\Form\Applier\ProductDraftChangesApplier;
use PimEnterprise\Bundle\WorkflowBundle\Repository\ProductDraftRepositoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Validator\Exception\ValidatorException;

/**
 * Inject current user product draft in a product before editing a product
 *
 * @author Gildas Quemener <gildas@akeneo.com>
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
     * @param UserContext                     $userContext
     * @param CatalogContext                  $catalogContext
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
     * Inject current product draft into the product
     *
     * @param GenericEvent $event
     */
    public function inject(GenericEvent $event)
    {
        $product = $event->getSubject();

        if ((null !== $user = $this->userContext->getUser())
            && (null !== $productDraft = $this->getProductDraft($product, $user->getUsername()))
        ) {
            try {
                $this->applier->apply($product, $productDraft);
            } catch (ValidatorException $e) {
                // Do nothing here at the moment
                //TODO: remove this try catch and load the form directly with the potential errors
            }
        }
    }

    /**
     * Get a product draft
     *
     * @param ProductInterface $product
     * @param string           $username
     *
     * @return \PimEnterprise\Bundle\WorkflowBundle\Model\ProductDraft|null
     */
    protected function getProductDraft(ProductInterface $product, $username)
    {
        return $this->repository->findUserProductDraft($product, $username);
    }
}
