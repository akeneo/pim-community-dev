<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Form\Subscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Pim\Bundle\CatalogBundle\Model\AbstractProductValue;
use PimEnterprise\Bundle\WorkflowBundle\Form\Comparator\ComparatorInterface;

/**
 * PimEnterprise\Bundle\WorkflowBundle\Form\Subscriber
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class CollectProductValuesSubscriber implements EventSubscriberInterface
{
    protected $comparator;
    private $changes = [];

    public function __construct(ComparatorInterface $comparator)
    {
        $this->comparator = $comparator;
    }

    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SUBMIT => 'collect',
        ];
    }

    public function collect(FormEvent $event)
    {
        $form = $event->getForm();
        if ('pim_product_edit' !== $form->getName()) {
            return;
        }

        $currentValues = $form->get('values')->getViewData();
        $data = $event->getData();
        foreach ($data['values'] as $key => $value) {
            if ($currentValues->containsKey($key)) {
                if (null !== $changes = $this->comparator->getChanges($currentValues->get($key), $value)) {
                    $this->changes['values'][$key] = $changes;
                }
            }
        }

        $this->removeValuesFromFormData($event);
    }

    public function getChanges()
    {
        return $this->changes;
    }

    protected function removeValuesFromFormData(FormEvent $event)
    {
        $data = $event->getData();
        unset($data['values']);
        $event->setData($data);
    }
}
