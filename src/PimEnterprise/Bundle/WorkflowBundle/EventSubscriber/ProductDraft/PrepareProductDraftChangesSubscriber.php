<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\EventSubscriber\ProductDraft;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use PimEnterprise\Bundle\WorkflowBundle\Event\ProductDraftEvents;
use PimEnterprise\Bundle\WorkflowBundle\Event\ProductDraftEvent;

/**
 * Merge values to keep previous product draft changes that
 * are not sent in the current request (like localized attributes or files)
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
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
     * Keep media changes when from previous product draft
     *
     * @param ProductDraftEvent $event
     */
    public function keepMedia(ProductDraftEvent $event)
    {
        $productDraft = $event->getProductDraft();
        $currentChanges = $productDraft->getChanges();
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
        $productDraft = $event->getProductDraft();
        $submittedChanges = $event->getChanges();

        $oldValue = array_merge(['values'=> []], $productDraft->getChanges());
        $newValue = array_merge(['values'=> []], $submittedChanges);

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
