<?php

namespace Oro\Bundle\ConfigBundle\Form\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;

class ConfigSubscriber implements EventSubscriberInterface
{
    /**
     * @var ConfigManager
     */
    protected $configManager;

    /**
     * @param ConfigManager $configManager
     */
    public function __construct(ConfigManager $configManager)
    {
        $this->configManager = $configManager;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::PRE_SUBMIT => 'preSubmit'
        );
    }

    /**
     * @param FormEvent $event
     */
    public function preSubmit(FormEvent $event)
    {
        $form = $event->getForm();

//        if (!empty($val['use_parent_scope_value'])) {
//
//        }

        $settingsData = $this->configManager->restoreDefaultOnSubmit($event->getData());
        $event->setData($settingsData);
    }
}
