<?php

namespace Oro\Bundle\EntityExtendBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;

class FieldType extends AbstractType
{
    protected $types = array(
        'string'     => 'String',
        'integer'    => 'Integer',
        'smallint'   => 'SmallInt',
        'bigint'     => 'BigInt',
        'boolean'    => 'Boolean',
        'decimal'    => 'Decimal',
        'date'       => 'Date',
        'text'       => 'Text',
        'float'      => 'Float',
        'oneToMany'  => 'Relation one to many',
        'manyToOne'  => 'Relation many to one',
        'manyToMany' => 'Relation many to many',
    );

    /**
     * @var ConfigManager
     */
    protected $configManager;

    public function __construct(ConfigManager $configManager)
    {
        $this->configManager = $configManager;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'fieldName',
            'text',
            array(
                'label' => 'Field Name',
                'block' => 'type',
            )
        );

        $extendProvider = $this->configManager->getProvider('extend');
        $entityConfig = $extendProvider->getConfig($options['class_name']);

        if ($entityConfig->is('relation')) {
            foreach ($entityConfig->get('relation') as $relation) {
                if ($relation['assign'] == true
                    && $relation['field_id']
                    && !$extendProvider->hasConfig(
                        $relation['field_id']->getClassName(),
                        $relation['field_id']->getFieldName()
                    )
                ) {
                    var_dump($relation);
                    //die('asd');
                }
            }
        }

        $builder->add(
            'type',
            'choice',
            array(
                'choices'     => $this->types,
                'empty_value' => 'Please choice type...',
                'block'       => 'type',
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setRequired(array('class_name'))
            ->setDefaults(
                array(
                    'require_js'   => array(),
                    'block_config' => array(
                        'type' => array(
                            'title'    => 'General',
                            'priority' => 1,
                        )
                    )
                )
            );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_entity_extend_field_type';
    }
}
