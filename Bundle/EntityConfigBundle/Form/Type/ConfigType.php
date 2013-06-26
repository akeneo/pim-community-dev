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
     * @param $items
     */
    public function __construct($items)
    {
        $this->items = $items;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        foreach ($this->items as $code => $config) {
            if (isset($config['form']) && isset($config['form']['type']) && isset($config['form']['options'])) {
                $builder->add($code, $config['form']['type'], $config['form']['options']);
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
