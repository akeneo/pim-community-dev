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
    const NAME = 'custom_entity_type';

    /**
     * @var ConfigManager
     */
    protected $configManager;

    protected $typeMap = array(
        'string'   => 'text',
        'integer'  => 'integer',
        'smallint' => 'integer',
        'bigint'   => 'integer',
        'boolean'  => 'choice',
        'decimal'  => 'number',
        'date'     => 'oro_date',
        'datetime' => 'oro_datetime',
        'text'     => 'textarea',
        'float'    => 'number',
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
            // TODO: refactor ConfigIdInterface to allow extracting of field name,
            // TODO: should be done in scope https://magecore.atlassian.net/browse/BAP-1722
            $extendConfig = $extendConfigProvider->getConfig($className, $formConfig->getId()->getFieldName());

            // TODO: Convert this check to method in separate helper service and reuse it in ExtendEntityExtension,
            // TODO: should be done in scope of https://magecore.atlassian.net/browse/BAP-1721
            if ($formConfig->get('is_enabled')
                && !$extendConfig->is('is_deleted')
                && $extendConfig->is('owner', ExtendManager::OWNER_CUSTOM)
                && !$extendConfig->is('state', ExtendManager::STATE_NEW)
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

                $builder->add(
                    Inflector::camelize($fieldConfigId->getFieldName()),
                    $this->typeMap[$fieldConfigId->getFieldType()],
                    $options
                );
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
        return self::NAME;
    }
}
