<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\ProductDraft;

use Pim\Bundle\CatalogBundle\Builder\ProductBuilder;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use PimEnterprise\Bundle\WorkflowBundle\Comparator\ComparatorInterface;
use PimEnterprise\Bundle\WorkflowBundle\Event\ChangeSetEvent;
use PimEnterprise\Bundle\WorkflowBundle\Event\ChangeSetEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Product change set computer during product draft workflow
 *
 * @author Gildas Quemener <gildas@akeneo.com>
 */
class ChangeSetComputer implements ChangeSetComputerInterface
{
    /** @var ComparatorInterface $comparator */
    protected $comparator;

    /** @var EventDispatcherInterface $eventDispatcher */
    protected $eventDispatcher;

    /** @var ProductBuilder */
    protected $productBuilder;

    /**
     * @param ComparatorInterface      $comparator
     * @param EventDispatcherInterface $eventDispatcher
     * @param ProductBuilder           $productBuilder
     */
    public function __construct(
        ComparatorInterface $comparator,
        EventDispatcherInterface $eventDispatcher,
        ProductBuilder $productBuilder
    ) {
        $this->comparator = $comparator;
        $this->eventDispatcher = $eventDispatcher;
        $this->productBuilder = $productBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function compute(ProductInterface $product, array $submittedData)
    {
        $changeSet = [];
        if (!isset($submittedData['values'])) {
            return $changeSet;
        }

        $this->productBuilder->addMissingProductValues($product);

        $currentValues = $product->getValues();
        foreach ($submittedData['values'] as $key => $data) {
            if ($currentValues->containsKey($key)) {
                $value = $currentValues->get($key);
                $changes = $this->prepareChange(
                    $value,
                    $this->comparator->getChanges($value, $data)
                );

                $changeSet['values'][$key] = $changes;
            }
        }

        return $changeSet;
    }

    /**
     * Delegates change preparation to event listeners/subscribers
     *
     * @param ProductValueInterface $value
     * @param null|array            $changes
     *
     * @return null|array
     */
    protected function prepareChange(ProductValueInterface $value, $changes)
    {
        $event = new ChangeSetEvent($value, $changes);
        $this->eventDispatcher->dispatch(ChangeSetEvents::PREPARE_CHANGE, $event);

        return $event->getChangeSet();
    }
}
