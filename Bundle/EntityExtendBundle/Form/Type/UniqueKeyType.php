<?php

namespace Oro\Bundle\EntityExtendBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

use Oro\Bundle\EntityConfigBundle\Config\FieldConfig;

class UniqueKeyType extends AbstractType
{
    /**
     * @var FieldConfig[]
     */
    protected $fields;

    public function __construct($fields)
    {
        $this->fields = $fields;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $choices = array_map(function (FieldConfig $field) {
            return ucfirst($field->getCode());
        }, $this->fields);

        $builder->add(
            'name',
            'text',
            array(
                'required'    => true,
            )
        );

        $builder->add(
            'key',
            'choice',
            array(
                'multiple'    => true,
                'choices'     => $choices,
                'required'    => true,
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_entity_extend_unique_key_type';
    }
}
