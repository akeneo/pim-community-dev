<?php

namespace Oro\Bundle\EntityConfigBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class ConfigType extends AbstractType
{
    /**
     * @var array
     */
    protected $items;

    /**
     * @var string
     */
    protected $fieldType;

    /**
     * @param $items
     * @param $fieldType
     */
    public function __construct($items, $fieldType)
    {
        $this->items     = $items;
        $this->fieldType = $fieldType;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        foreach ($this->items as $code => $config) {
            if (isset($config['form']) && isset($config['form']['type'])) {
                $options               = isset($config['form']['options']) ? $config['form']['options'] : array();
                $options['field_type'] = $this->fieldType;
                $builder->add($code, $config['form']['type'], $options);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_entity_config_config_type';
    }
}
