<?php

namespace PimEnterprise\Bundle\WorkflowBundle\EventSubscriber\Proposition;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use PimEnterprise\Bundle\WorkflowBundle\Proposition\PropositionEvents;
use PimEnterprise\Bundle\WorkflowBundle\Proposition\PropositionEvent;

/**
 * Merge values to keep previous proposition changes that
 * are not sent in the current request (like localized attributes or files)
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class MergeValuesSubscriber implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            PropositionEvents::PRE_UPDATE => 'merge',
        ];
    }

    /**
     * Merge old and new values
     *
     * @param PropositionEvent $event
     */
    public function merge(PropositionEvent $event)
    {
        $proposition = $event->getProposition();
        $changes = $event->getChanges();

        $oldValue = array_merge(['values'=>[]], $proposition->getChanges());
        $newValue = array_merge(['values'=>[]], $changes);

        $proposition->setChanges(
            [
                'values' => array_merge(
                    $oldValue['values'],
                    $newValue['values']
                )
            ]
        );
    }
}
