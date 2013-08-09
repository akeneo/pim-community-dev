<?php

namespace Oro\Bundle\EntityConfigBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Oro\Bundle\EntityConfigBundle\Entity\FieldConfigModel;
use Oro\Bundle\EntityConfigBundle\Entity\EntityConfigModel;

use Oro\Bundle\EntityConfigBundle\ConfigManager;
use Oro\Bundle\EntityConfigBundle\Provider\PropertyConfigContainer;
use Oro\Bundle\EntityConfigBundle\Form\EventListener\ConfigSubscriber;

class ConfigType extends AbstractType
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
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $configModel = $options['config_model'];
        $data        = array();

        if ($configModel instanceof FieldConfigModel) {
            $className  = $configModel->getEntity()->getClassName();
            $fieldName  = $configModel->getFieldName();
            $fieldType  = $configModel->getType();
            $configType = PropertyConfigContainer::TYPE_FIELD;
        } else {
            $className  = $configModel->getClassName();
            $fieldName  = null;
            $fieldType  = null;
            $configType = PropertyConfigContainer::TYPE_ENTITY;

            $builder->add('className', 'text', array(
                'read_only' => true,
                'block'     => 'entity',
                'required'  => false
            ));
            $data['className'] = $className;
        }

        foreach ($this->configManager->getProviders() as $provider) {
            if ($provider->getPropertyConfig()->hasForm($configType, $fieldType)) {
                $builder->add(
                    $provider->getScope(),
                    new ConfigScopeType($provider->getPropertyConfig()->getFormItems($configType, $fieldType)),
                    array(
                        'block_config' => (array) $provider->getPropertyConfig()->getFormBlockConfig($configType)
                    )
                );
                $data[$provider->getScope()] = $provider->getConfig($className, $fieldName)->getValues();
            }
        }

        $builder->setData($data);

        $builder->addEventSubscriber(new ConfigSubscriber($this->configManager));
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setRequired(array('config_model'));

        $resolver->setAllowedTypes(
            array(
                'config_model' => 'Oro\Bundle\EntityConfigBundle\Entity\AbstractConfigModel'
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_entity_config_type';
    }
}
