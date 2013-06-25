<?php

namespace Oro\Bundle\EntityExtendBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;

class FieldType extends AbstractType
{
    /**
     * @var ConfigProvider
     */
    protected $extendConfigProvider;

    protected $types = array(
        'string',
        'text',
        'integer',
        'float',
        'decimal',
        'boolean',
        'datetime',
        'date'
    );

    protected $options = array(
        'length'    => array('string'),
        'unique'    => array(),
        'nullable'  => array(),
        'precision' => array('decimal'),
        'scale'     => array('decimal'),
    );

    /**
     * @param ConfigProvider $extendConfigProvider
     */
    public function __construct(ConfigProvider $extendConfigProvider)
    {
        $this->extendConfigProvider = $extendConfigProvider;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('type', 'choice', array(
            'choices' => array_combine(array_reverse($this->types), $this->types)
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
