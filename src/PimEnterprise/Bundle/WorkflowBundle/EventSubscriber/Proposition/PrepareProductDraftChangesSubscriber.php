<?php

namespace PimEnterprise\Bundle\WorkflowBundle\EventSubscriber\Proposition;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use PimEnterprise\Bundle\WorkflowBundle\Event\ProductDraftEvents;
use PimEnterprise\Bundle\WorkflowBundle\Event\ProductDraftEvent;

/**
 * Merge values to keep previous proposition changes that
 * are not sent in the current request (like localized attributes or files)
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class PrepareProductDraftChangesSubscriber implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            ProductDraftEvents::PRE_UPDATE => [
                ['keepMedia', 128],
                ['mergeValues', 64],
                ['removeNullValues', 0],
                ['cleanEmptyChangeSet', -128],
                ['sortValues', -128],
            ],
        ];
    }

    /**
     * Keep media changes when from previous proposition
     *
     * @param ProductDraftEvent $event
     */
    public function keepMedia(ProductDraftEvent $event)
    {
        $proposition = $event->getProposition();
        $currentChanges = $proposition->getChanges();
        if (!isset($currentChanges['values'])) {
            return;
        }
        $submittedChanges = $event->getChanges();
        foreach ($currentChanges['values'] as $key => $value) {
            if (isset($value['media']) && !isset($submittedChanges['values'][$key])) {
                $submittedChanges['values'][$key] = $value;
            }
        }

        $event->setChanges($submittedChanges);
    }

    /**
     * Merge old and new values
     *
     * @param ProductDraftEvent $event
     */
    public function mergeValues(ProductDraftEvent $event)
    {
        $proposition = $event->getProposition();
        $submittedChanges = $event->getChanges();

        $oldValue = array_merge(['values'=>[]], $proposition->getChanges());
        $newValue = array_merge(['values'=>[]], $submittedChanges);

        $event->setChanges(
            [
                'values' => array_merge(
                    $oldValue['values'],
                    $newValue['values']
                )
            ]
        );
    }

    /**
     * Unset removed changes
     *
     * @param ProductDraftEvent $event
     *
     * @return null
     */
    public function removeNullValues(ProductDraftEvent $event)
    {
        $submittedChanges = $event->getChanges();
        if (!isset($submittedChanges['values'])) {
            return;
        }

        foreach ($submittedChanges['values'] as $key => $value) {
            if (null === $value) {
                unset($submittedChanges['values'][$key]);
            }
        }

        $event->setChanges($submittedChanges);
    }

    /**
     * Sort values for esthetic purpose
     *
     * @param ProductDraftEvent $event
     */
    public function sortValues(ProductDraftEvent $event)
    {
        $submittedChanges = $event->getChanges();
        if (!isset($submittedChanges['values'])) {
            return;
        }

        $values = $submittedChanges['values'];
        ksort($values);
        $submittedChanges['values'] = $values;

        $event->setChanges($submittedChanges);
    }

    /**
     * Clean the whole change set when no value change was submitted
     *
     * @param ProductDraftEvent $event
     */
    public function cleanEmptyChangeSet(ProductDraftEvent $event)
    {
        $submittedChanges = $event->getChanges();
        if (!isset($submittedChanges['values'])) {
            return;
        }

        if (empty($submittedChanges['values'])) {
            $event->setChanges([]);
        }
    }
}
