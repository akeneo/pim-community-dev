<?php

namespace Oro\Bundle\EntityExtendBundle\Form\Type;

use Doctrine\Common\Collections\ArrayCollection;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

use Oro\Bundle\EntityConfigBundle\Config\FieldConfig;

class UniqueKeyType extends AbstractType
{
    /**
     * @var FieldConfig[]|ArrayCollection
     */
    protected $fields;

    public function __construct($fields)
    {
        $this->fields = $fields;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $choices = $this->fields->map(function (FieldConfig $field) {
            return ucfirst($field->getCode());
        });

        $builder->add(
            'name',
            'text',
            array(
                'required' => true,
            )
        );

        $builder->add(
            'key',
            'choice',
            array(
                'multiple' => true,
                'choices'  => $choices->toArray(),
                'required' => true,
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
