<?php

namespace PimEnterprise\Bundle\WorkflowBundle\EventSubscriber\Proposition;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use PimEnterprise\Bundle\WorkflowBundle\Event\PropositionEvents;
use PimEnterprise\Bundle\WorkflowBundle\Event\PropositionEvent;
use PimEnterprise\Bundle\WorkflowBundle\Proposition\ChangesCollectorInterface;

/**
 * Merge values to keep previous proposition changes that
 * are not sent in the current request (like localized attributes or files)
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class ResetValuesSubscriber implements EventSubscriberInterface
{
    /** @var ChangesCollectorInterface */
    protected $collector;

    /**
     * @param ChangesCollectorInterface $collector
     */
    public function __construct(ChangesCollectorInterface $collector)
    {
        $this->collector = $collector;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            PropositionEvents::PRE_UPDATE => ['reset', 128],
        ];
    }

    /**
     * Unset removed changes
     *
     * @param PropositionEvent $event
     */
    public function reset(PropositionEvent $event)
    {
        $proposition = $event->getProposition();
        $changes = $proposition->getChanges();
        if (!isset($changes['values'])) {
            return;
        }

        foreach ($this->collector->getKeysToRemove() as $key) {
            // File is the only field type that is not resent on every request
            if (isset($changes['values'][$key]) && !isset($changes['values'][$key]['media'])) {
                unset($changes['values'][$key]);
            }
        }

        $proposition->setChanges($changes);
    }
}
