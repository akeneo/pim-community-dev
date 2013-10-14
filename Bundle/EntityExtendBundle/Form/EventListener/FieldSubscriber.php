<?php

namespace Oro\Bundle\EntityExtendBundle\Form\EventListener;

use Oro\Bundle\EntityConfigBundle\Entity\EntityConfigModel;
use Oro\Bundle\EntityConfigBundle\Entity\FieldConfigModel;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\Request;

use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;

class FieldSubscriber implements EventSubscriberInterface
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
        $form = $event->getForm();
        $data = $form->getParent()->getData();
        $name = $form->getName();

        $config = $form->getParent()->get($name)->getConfig()->getOptions();
        if (array_key_exists('auto_initialize', $config)) {
            $config['auto_initialize'] = false;
        }

        $choices = array();

        $className = $form->getParent()->get('target_entity')->getData();
        if (null == $className) {
            $className = $this->request->request->get('oro_entity_config_type[extend][target_entity]', null, true);
        }

        /*
        if ($className) {
            /** @var EntityConfigModel $entity * /
            $entity = $this->configManager->getEntityManager()
                ->getRepository(EntityConfigModel::ENTITY_NAME)
                ->findOneBy(array('className' => $className));

            if ($entity) {
                /** @var ConfigProvider $entityConfigProvider * /
                $entityConfigProvider = $this->configManager->getProvider('entity');

                $entityFields = $this->configManager->getEntityManager()
                    ->getRepository(FieldConfigModel::ENTITY_NAME)
                    ->findBy(
                        array(
                            'entity' => $entity->getId(),
                            'type'   => 'string'
                        ),
                        array('fieldName' => 'ASC')
                    );

                foreach ($entityFields as $field) {
                    $label = $entityConfigProvider->getConfig($className, $field->getFieldName())->get('label');
                    $choices[$field->getFieldName()] = $label ? : $field->getFieldName();
                }
            }
        }
        */

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

        $entities = $this->configManager->getIds('entity');
        foreach ($entities as $entity) {
            $entityName = $moduleName = '';

            if ($entity->getClassName() != $entityClassName) {

                $className = explode('\\', $entity->getClassName());
                if (count($className) > 1) {
                    foreach ($className as $i => $name) {
                        if (count($className) - 1 == $i) {
                            $entityName = $name;
                        } elseif (!in_array($name, array('Bundle', 'Entity'))) {
                            $moduleName .= $name;
                        }
                    }
                }

                $options[$entity->getClassName()] = $moduleName . ':' . $entityName;
            }
        }





        if (count($choices)) {
            unset($config['choice_list']);
            unset($config['choices']);

            $config['choices'] = $choices;
        }

        $form->getParent()->add($name, 'choice', $config);

        if (isset($data[$name]) && empty($data[$name])) {
            $data['target_field'] = $this->request->request->get(
                'oro_entity_config_type[extend]['.$name.']',
                null,
                true
            );
            $form->getParent()->setData($data);
        }
    }
}
