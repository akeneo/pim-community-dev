<?php

namespace Oro\Bundle\EntityExtendBundle\Form\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\Request;

use Oro\Bundle\EntityConfigBundle\Config\Id\EntityConfigId;
use Oro\Bundle\EntityConfigBundle\Entity\FieldConfigModel;
use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;

class TargetSubscriber implements EventSubscriberInterface
{
    protected $request;
    protected $configManager;

    public function __construct(Request $request, ConfigManager $configManager)
    {
        $this->request       = $request;
        $this->configManager = $configManager;
    }

    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::PRE_SET_DATA => 'preSetSubmitData',
            FormEvents::PRE_SUBMIT   => 'preSetSubmitData'
        );
    }

    public function preSetSubmitData(FormEvent $event)
    {
        $form    = $event->getForm();
        $choices = array();

        $entityIds = $this->configManager->getIds('extend');

        $fieldConfigModel = $form->getRoot()->getConfig()->getOption('config_model');

        if (in_array($fieldConfigModel->getType(), array('oneToMany', 'manyToMany'))) {
            $cm = $this->configManager;
            $entityIds = array_filter(
                $entityIds,
                function (EntityConfigId $configId) use ($cm) {
                    return $cm->getConfig($configId)->is('is_extend');
                }
            );
        }

        $config = $form->getParent()->get($form->getName())->getConfig()->getOptions();
        if (array_key_exists('auto_initialize', $config)) {
            $config['auto_initialize'] = false;
        }

        if (null === $this->request->get('entity')) {
            /** @var FieldConfigModel $entity */
            $entity = $this->configManager->getEntityManager()
                ->getRepository(FieldConfigModel::ENTITY_NAME)
                ->find($this->request->get('id'));

            $entityClassName = $entity->getEntity()->getClassName();
            $config['disabled'] = true;
        } else {
            $entityClassName = $this->request->get('entity')->getClassName();
        }

        foreach ($entityIds as $entity) {
            $entityName = $moduleName = '';
            if ($entity->getClassName() != $entityClassName) {
                $className  = explode('\\', $entity->getClassName());
                if (count($className) > 1) {
                    foreach ($className as $i => $name) {
                        if (count($className) - 1 == $i) {
                            $entityName = $name;
                        } elseif (!in_array($name, array('Bundle', 'Entity'))) {
                            $moduleName .= $name;
                        }
                    }
                }

                $choices[$entity->getClassName()] = $moduleName . ':' . $entityName;
            }
        }

        if (count($choices)) {
            unset($config['choice_list']);
            unset($config['choices']);

            $config['choices'] = $choices;
        }

        $form->getParent()->add($form->getName(), 'choice', $config);
    }
}
