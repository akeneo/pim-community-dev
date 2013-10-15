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

class TargetFieldSubscriber implements EventSubscriberInterface
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
        $data    = $form->getParent()->getData();
        $choices = array();

        $config = $form->getParent()->get('target_field')->getConfig()->getOptions();
        if (array_key_exists('auto_initialize', $config)) {
            $config['auto_initialize'] = false;
        }

        $className = $form->getParent()->get('target_entity')->getData();
        if (null == $className) {
            $className = $this->request->request->get('oro_entity_config_type[extend][target_entity]', null, true);
        }

        if ($className) {
            /** @var EntityConfigModel $entity */
            $entity = $this->configManager->getEntityManager()
                ->getRepository(EntityConfigModel::ENTITY_NAME)
                ->findOneBy(array('className' => $className));

            if ($entity) {
                /** @var ConfigProvider $entityConfigProvider */
                $entityConfigProvider = $this->configManager->getProvider('entity');

                $entityFields = $this->configManager->getEntityManager()
                    ->getRepository(FieldConfigModel::ENTITY_NAME)
                    ->findBy(
                        array('entity' => $entity->getId(), 'type' => 'string'),
                        array('fieldName' => 'ASC')
                    );

                foreach ($entityFields as $field) {
                    $label = $entityConfigProvider->getConfig($className, $field->getFieldName())->get('label');
                    $choices[$field->getFieldName()] = $label ? : $field->getFieldName();
                }
            }
        }

        if (count($choices)) {
            unset($config['choice_list']);
            unset($config['choices']);

            $config['choices'] = $choices;
        }

        $form->getParent()->add('target_field', 'choice', $config);

        if (isset($data['target_field']) && empty($data['target_field'])) {
            $data['target_field'] = $this->request->request->get(
                'oro_entity_config_type[extend][target_field]',
                null,
                true
            );
            $form->getParent()->setData($data);
        }
    }
}
