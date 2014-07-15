<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Proposition;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Pim\Bundle\CatalogBundle\Model\AbstractProductValue;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use PimEnterprise\Bundle\WorkflowBundle\Comparator\ComparatorInterface;
use PimEnterprise\Bundle\WorkflowBundle\Event\ChangeSetEvent;
use PimEnterprise\Bundle\WorkflowBundle\Event\ChangeSetEvents;

/**
 * Product change set computer during proposition workflow
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class ChangeSetComputer implements ChangeSetComputerInterface
{
    /** @var ComparatorInterface $comparator */
    protected $comparator;

    /** @var EventDispatcherInterface $eventDispatcher */
    protected $eventDispatcher;

    /**
     * @param ComparatorInterface      $comparator
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        ComparatorInterface $comparator,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->comparator = $comparator;
        $this->eventDispatcher = $eventDispatcher;
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
     * @param AbstractProductValue $value
     * @param null|array           $changes
     *
     * @return null|array
     */
    protected function prepareChange(AbstractProductValue $value, $changes)
    {
        $event = new ChangeSetEvent($value, $changes);
        $this->eventDispatcher->dispatch(ChangeSetEvents::PREPARE_CHANGE, $event);

        return $event->getChangeSet();
    }
}
