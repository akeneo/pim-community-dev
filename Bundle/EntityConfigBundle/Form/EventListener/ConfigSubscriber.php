<?php

namespace Oro\Bundle\EntityConfigBundle\Form\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

use Oro\Bundle\EntityConfigBundle\Entity\FieldConfigModel;
use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;

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
        $options     = $event->getForm()->getConfig()->getOptions();
        $configModel = $options['config_model'];

        if ($configModel instanceof FieldConfigModel) {
            $className = $configModel->getEntity()->getClassName();
            $fieldName = $configModel->getFieldName();
        } else {
            $fieldName = null;
            $className = $configModel->getClassName();
        }

        $data    = $event->getData();

        foreach ($this->configManager->getProviders() as $provider) {
            if (isset($data[$provider->getScope()])) {
                $config = $provider->getConfig($className, $fieldName);

                $config->setValues($data[$provider->getScope()]);

                $this->configManager->persist($config);
            }
        }

        if ($event->getForm()->isValid()) {
            $this->configManager->flush();
        }
    }
}
