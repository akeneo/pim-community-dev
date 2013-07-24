<?php

namespace Oro\Bundle\EntityExtendBundle\Form\Type;

use Oro\Bundle\EntityConfigBundle\Config\FieldConfig;
use Oro\Bundle\EntityConfigBundle\ConfigManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class FieldType extends AbstractType
{
    protected $types = array(
        'string',
        'integer',
        'smallint',
        'bigint',
        'boolean',
        'decimal',
        'date',
        'time',
        'datetime',
        'text',
        'float',
    );

    /**
     * @var ConfigManager
     */
    protected $configManager;

    public function __construct(ConfigManager $configManager)
    {
        $this->configManager = $configManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('code', 'text', array(
            'label' => 'Field Name',
            'block' => 'type',
        ));
        $builder->add('type', 'choice', array(
            'choices'     => array_combine(array_values($this->types), $this->types),
            'empty_value' => 'Please choice type...',
            'block'       => 'type',
        ));
        $builder->add('options', 'oro_entity_config_config_field_type', array(
            'class_name' => $options['class_name'],
            'field_id'   => '',
            'field_name' => '',
            'field_type' => '',
            'block'      => 'options',
        ));

        $formFactory   = $builder->getFormFactory();
        $className     = $options['class_name'];
        $configManager = $this->configManager;

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) use ($formFactory, $className, $configManager) {
            $event->getForm()->remove('options');

            $data        = $event->getData();
            $formOptions = array(
                'class_name'      => $className,
                'field_id'        => '',
                'field_name'      => $data['code'],
                'field_type'      => $data['type'],
                'block'           => 'options',
                'auto_initialize' => false
            );

            $event->getForm()->add($formFactory->createNamed('options', 'oro_entity_config_config_field_type', $data['options'], $formOptions));

            $configManager->getConfig($className, 'extend')->addField(new FieldConfig($className, $data['code'], $data['type'], 'extend'));
        });
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'class_name'   => '',
            'block_config' => array(
                'type' => array(
                    'title'    => 'Doctrine Type',
                    'priority' => 1,
                )
            )
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_entity_extend_field_type';
    }
}
