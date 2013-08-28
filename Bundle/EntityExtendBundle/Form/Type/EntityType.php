<?php

namespace Oro\Bundle\EntityExtendBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class EntityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'className',
            'text',
            array(
                'label'    => 'Name',
                'block'    => 'entity',
                'subblock' => 'second'
            )
        );
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class'   => 'Oro\Bundle\EntityConfigBundle\Entity\EntityConfigModel',
                'block_config' => array(
                    'entity' => array(
                        'title' => 'General',
                        'subblocks' => array(
                            'second' => array(
                                'priority' => 10
                            )
                        )
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
        return 'oro_entity_extend_entity_type';
    }
}
