<?php

namespace Oro\Bundle\EntityExtendBundle\Form\Type;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\Request;

use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityConfigBundle\Entity\FieldConfigModel;
use Oro\Bundle\EntityConfigBundle\Entity\EntityConfigModel;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;

class TargetFieldType extends AbstractType
{
    /** @var  ConfigManager */
    protected $configManager;

    /** @var  Request */
    protected $request;

    /**
     * @param ConfigManager $configManager
     * @param Request $request
     */
    public function __construct(ConfigManager $configManager, Request $request)
    {
        $this->configManager = $configManager;
        $this->request       = $request;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $ff            = $builder->getFormFactory();
        $request       = $this->request;
        $configManager = $this->configManager;

        $event = function (FormEvent $event) use ($ff, $request, $configManager) {
            $form = $event->getForm();
            $data = $form->getParent()->getData();

            $config = $form->getParent()->get('target_field')->getConfig()->getOptions();
            if (array_key_exists('auto_initialize', $config)) {
                $config['auto_initialize'] = false;
            }

            $choices = array();

            $className = $form->getParent()->get('target_entity')->getData();
            if (null == $className) {
                $className = $request->request->get(
                    'oro_entity_config_type[extend][target_entity]',
                    null,
                    true
                );
            }
            //$className = str_replace('_', '\\', $className);

            if ($className) {
                /** @var EntityConfigModel $entity */
                $entity = $configManager->getEntityManager()
                    ->getRepository(EntityConfigModel::ENTITY_NAME)
                    ->findOneBy(array('className' => $className));

                if ($entity) {
                    /** @var ConfigProvider $entityConfigProvider */
                    $entityConfigProvider = $configManager->getProvider('entity');

                    $entityFields = $configManager->getEntityManager()
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

            if (count($choices)) {
                unset($config['choice_list']);
                unset($config['choices']);

                $config['choices'] = $choices;
            }

            $form->getParent()->add('target_field', 'choice', $config);

            if (isset($data['target_field']) && empty($data['target_field'])) {
                $data['target_field'] = $request->request->get(
                    'oro_entity_config_type[extend][target_field]',
                    null,
                    true
                );
                $form->getParent()->setData($data);
            }
        };

        /**
         * Register the function above as EventListener on PreSet and PreSubmit
         */
        $builder->addEventListener(FormEvents::PRE_SET_DATA, $event);
        $builder->addEventListener(FormEvents::PRE_SUBMIT, $event);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'choice';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_entity_target_field_type';
    }
}
