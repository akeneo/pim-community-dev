<?php

namespace Oro\Bundle\EntityConfigBundle\Form\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

use Oro\Bundle\EntityConfigBundle\ConfigManager;

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
            FormEvents::POST_SUBMIT => 'postSubmit'
        );
    }

    /**
     * @param FormEvent $event
     */
    public function postSubmit(FormEvent $event)
    {
        $options   = $event->getForm()->getConfig()->getOptions();
        $className = $options['class_name'];
        $fieldName = isset($options['field_name']) ? $options['field_name'] : null;
        $data      = $event->getData();

        foreach ($this->configManager->getProviders() as $provider) {
            if (isset($data[$provider->getScope()])) {
                if ($fieldName) {
                    $config = $provider->getFieldConfig($className, $fieldName);
                } else {
                    $config = $provider->getConfig($className);
                }
                $config->setValues($data[$provider->getScope()]);
                //TODO::look after a EntityConfig changes in configManager
                $this->configManager->persist($config);
            }
        }

        $this->configManager->flush();
    }
}
