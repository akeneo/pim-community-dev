<?php

namespace Oro\Bundle\EntityConfigBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Oro\Bundle\EntityConfigBundle\ConfigManager;
use Oro\Bundle\EntityConfigBundle\Form\EventListener\ConfigSubscriber;

class ConfigFieldType extends AbstractType
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
        $className = $options['class_name'];
        $fieldName = $options['field_name'];
        $fieldType = $options['field_type'];
        $fieldId   = $options['field_id'];

        $data = array(
            'id' => $fieldId,
        );

        $builder->add('id', 'hidden');

        foreach ($this->configManager->getProviders() as $provider) {
            if ($provider->getConfigContainer()->hasFieldForm()) {
                $builder->add(
                    $provider->getScope(),
                    new ConfigType($provider->getConfigContainer()->getFieldItems(), $fieldType),
                    array(
                        'block_config' => (array) $provider->getConfigContainer()->getEntityFormBlockConfig()
                    )
                );
                $data[$provider->getScope()] = $provider->getFieldConfig($className, $fieldName)->getValues();
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
        $resolver->setRequired(
            array(
                'class_name',
                'field_name',
                'field_type',
                'field_id',
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_entity_config_config_field_type';
    }
}
