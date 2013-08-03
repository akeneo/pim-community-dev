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
                $config = $provider->getConfig($className, $fieldName);

                $config->setValues($data[$provider->getScope()]);

                $this->configManager->persist($config);
            }
        }

        $this->configManager->flush();
    }
}
