<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\Applier;

use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Updater\ProductUpdater;
use PimEnterprise\Bundle\WorkflowBundle\Event\ProductDraftEvents;
use PimEnterprise\Bundle\WorkflowBundle\Model\ProductDraft;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Apply a draft
 *
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
class ProductDraftApplier implements ProductDraftApplierInterface
{
    /** @var ProductUpdater */
    protected $productUpdater;

    /** @var EventDispatcherInterface */
    protected $dispatcher;

    /**
     * @param ProductUpdater           $productUpdater
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(ProductUpdater $productUpdater, EventDispatcherInterface $dispatcher)
    {
        $this->productUpdater = $productUpdater;
        $this->dispatcher = $dispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(ProductInterface $product, ProductDraft $productDraft)
    {
        $this->dispatcher->dispatch(ProductDraftEvents::PRE_APPLY, new GenericEvent($productDraft));

        $changes = $productDraft->getChanges();

        if (!isset($changes['values'])) {
            return null;
        }

        foreach ($changes['values'] as $code => $values) {
            foreach ($values as $value) {
                $this->productUpdater->setData(
                    $product,
                    $code,
                    $value['value'],
                    ['locale' => $value['locale'], 'scope' => $value['scope']]
                );
            }
        }

        $this->dispatcher->dispatch(ProductDraftEvents::POST_APPLY, new GenericEvent($productDraft));
    }
}
