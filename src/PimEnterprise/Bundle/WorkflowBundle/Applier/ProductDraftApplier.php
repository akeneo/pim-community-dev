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

use Akeneo\Component\StorageUtils\Updater\PropertySetterInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use PimEnterprise\Bundle\WorkflowBundle\Event\ProductDraftEvents;
use PimEnterprise\Bundle\WorkflowBundle\Model\ProductDraftInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Apply a draft
 *
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
class ProductDraftApplier implements ProductDraftApplierInterface
{
    /** @var PropertySetterInterface */
    protected $propertySetter;

    /** @var EventDispatcherInterface */
    protected $dispatcher;

    /**
     * @param PropertySetterInterface  $propertySetter
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(PropertySetterInterface $propertySetter, EventDispatcherInterface $dispatcher)
    {
        $this->propertySetter = $propertySetter;
        $this->dispatcher = $dispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(ProductInterface $product, ProductDraftInterface $productDraft)
    {
        $this->dispatcher->dispatch(ProductDraftEvents::PRE_APPLY, new GenericEvent($productDraft));

        $changes = $productDraft->getChanges();

        if (!isset($changes['values'])) {
            return;
        }

        foreach ($changes['values'] as $code => $values) {
            foreach ($values as $value) {
                $this->propertySetter->setData(
                    $product,
                    $code,
                    $value['data'],
                    ['locale' => $value['locale'], 'scope' => $value['scope']]
                );
            }
        }

        $this->dispatcher->dispatch(ProductDraftEvents::POST_APPLY, new GenericEvent($productDraft));
    }
}
