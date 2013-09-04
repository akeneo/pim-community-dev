<?php

namespace Oro\Bundle\SecurityBundle\Form\EventListener;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class EntityRowSubscriber
 *
 * Check ACL data array and delete fields without access
 *
 * @package Oro\Bundle\SecurityBundle\Form\EventListener
 */
class EntityRowSubscriber implements EventSubscriberInterface
{
    protected $fieldsConfig;


    public function __construct($fieldsConfig)
    {
        $this->fieldsConfig = $fieldsConfig;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(FormEvents::PRE_SET_DATA => 'preSetData');
    }

    /**
     * @param FormEvent $event
     */
    public function preSetData(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();

        foreach($this->fieldsConfig as $fieldName => $fieldConfig) {
            if (isset($fieldConfig['need_check']) && $fieldConfig['need_check']) {
                $this->checkField($fieldName, $form, $data);
            }
        }
    }

    protected function checkField($fieldName, $form, $data)
    {
        if (!isset($data[$fieldName])) {
            $form->remove($fieldName);
        }
    }
}
