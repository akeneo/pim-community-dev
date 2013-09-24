<?php

namespace Oro\Bundle\EntityBundle\Form\Type;

use Doctrine\Common\Util\Inflector;
use Oro\Bundle\EntityExtendBundle\Extend\ExtendManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityConfigBundle\Config\Id\FieldConfigIdInterface;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;

use Oro\Bundle\EntityExtendBundle\Tools\Generator;

class CustomEntityType extends AbstractType
{
    /**
     * @var ConfigManager
     */
    protected $configManager;

    protected $typeMap = array(
        'string'     => 'text',
        'integer'    => 'integer',
        'smallint'   => 'integer',
        'bigint'     => 'integer',
        'boolean'    => 'choice',
        'decimal'    => 'number',
        'date'       => 'oro_date',
        'datetime'   => 'oro_datetime',
        'text'       => 'textarea',
        'float'      => 'number',
        'oneToMany'  => 'integer',
        'manyToOne'  => 'oro_user_select',
        'manyToMany' => 'integer',
    );

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
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $className = $options['class_name'];

        /** @var ConfigProvider $formConfigProvider */
        $formConfigProvider = $this->configManager->getProvider('form');
        $formConfigs        = $formConfigProvider->getConfigs($className);

        /** @var ConfigProvider $entityConfigProvider */
        $entityConfigProvider = $this->configManager->getProvider('entity');

        /** @var ConfigProvider $extendConfigProvider */
        $extendConfigProvider = $this->configManager->getProvider('extend');

        foreach ($formConfigs as $formConfig) {
            $extendConfig = $extendConfigProvider->getConfig($className, $formConfig->getId()->getFieldName());
            if ($formConfig->get('is_enabled')
                && !$extendConfig->is('is_deleted')
                && $extendConfig->is('owner', ExtendManager::OWNER_CUSTOM)
                && !$extendConfig->is('state', ExtendManager::STATE_NEW)
                && !$extendConfig->is('state', ExtendManager::STATE_DELETED)
                && !in_array($formConfig->getId()->getFieldType(), array('ref-one', 'ref-many'))
                && $builder->getForm()->getName() != $this->getName()
            ) {
                /** @var FieldConfigIdInterface $fieldConfigId */
                $fieldConfigId = $formConfig->getId();

                $entityConfig = $entityConfigProvider->getConfig(
                    $fieldConfigId->getClassName(),
                    $fieldConfigId->getFieldName()
                );

                $options = array(
                    'label'    => $entityConfig->get('label'),
                    'required' => false,
                    'block'    => 'general',
                );

                if ($fieldConfigId->getFieldType() == 'boolean') {
                    $options['empty_value'] = false;
                    $options['choices']     = array('No', 'Yes');
                }

                if (in_array($fieldConfigId->getFieldType(), array('oneToMany', 'manyToOne', 'manyToMany'))) {
                    $builder->add(
                        $fieldConfigId->getFieldName(),
                        'oro_entity_select_type',
                        array(
                            'data_class' => $extendConfig->get('target_entity'),
                            'compound' => true
                        )
                    );
                } else {
                    $builder->add(
                        Inflector::camelize($fieldConfigId->getFieldName()),
                        $this->typeMap[$fieldConfigId->getFieldType()],
                        $options
                    );
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setRequired(array('class_name'));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'custom_entity_type';
    }
}
