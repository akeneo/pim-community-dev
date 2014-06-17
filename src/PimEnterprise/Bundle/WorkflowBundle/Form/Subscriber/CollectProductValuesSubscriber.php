<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Form\Subscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use PimEnterprise\Bundle\WorkflowBundle\Form\Comparator\ComparatorInterface;

/**
 * A collector of changes that a client is sending to a product edit form
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class CollectProductValuesSubscriber implements EventSubscriberInterface
{
    /** @var ComparatorInterface */
    protected $comparator;

    /** @var array */
    private $changes = [];

    /**
     * @param ComparatorInterface $comparator
     */
    public function __construct(ComparatorInterface $comparator)
    {
        $this->comparator = $comparator;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SUBMIT => 'collect',
        ];
    }

    /**
     * Collect changes that client sent to the product values
     *
     * @param FormEvent $event
     */
    public function collect(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();
        if ('pim_product_edit' !== $form->getName() || !array_key_exists('values', $data)) {
            return;
        }

        $currentValues = $form->get('values')->getViewData();
        foreach ($data['values'] as $key => $value) {
            // TODO (2014-06-16 15:45 by Gildas): Values that are sent and not available in the product
            // values should be stored in the changeset, shouldn't they?
            if ($currentValues->containsKey($key)) {
                if (null !== $changes = $this->comparator->getChanges($currentValues->get($key), $value)) {
                    $this->changes['values'][$key] = $changes;
                }
            }
        }
    }

    /**
     * Get the collected changes sent to the product values
     *
     * @return array
     */
    public function getChanges()
    {
        return $this->changes;
    }
}
