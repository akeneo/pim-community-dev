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
                $items = $provider->getConfigContainer()->getFieldItems();

                $defaultValues = $provider->getConfigContainer()->getFieldDefaultValues();

                $allowedTypes = array_map(
                    function ($item) use ($fieldType) {
                        if (isset($item['form']['options']['allowed_type'])) {
                            return array_map('trim', explode(',', $item['form']['options']['allowed_type']));
                        }

                        return false;
                    },
                    $items
                );

                foreach ($allowedTypes as $key => $allowedType) {
                    if (isset($defaultValues[$key]) && is_array($allowedType) && !in_array($fieldType, $allowedType)) {
                        unset($defaultValues[$key]);
                    }
                }

                foreach ($provider->getConfigContainer()->getFieldRequiredPropertyValues() as $code => $property) {
                    list($scope, $propertyName) = explode('.', $property['property_path']);

                    if ($this->configManager->getProvider($scope)->hasFieldConfig($className, $fieldName)) {
                        $value = $this->configManager->getProvider($scope)->getFieldConfig($className, $fieldName)->get($propertyName);
                        if ($value !== null && $value != $property['value']) {
                            unset($items[$code]);
                        }
                    }
                }

                $builder->add(
                    $provider->getScope(),
                    new ConfigType($items, $fieldType),
                    array('block_config' => (array) $provider->getConfigContainer()->getFieldFormBlockConfig())
                );

                $values = $provider->getFieldConfig($className, $fieldName)->getValues();

                $data[$provider->getScope()] = array_merge($defaultValues, $values);
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
