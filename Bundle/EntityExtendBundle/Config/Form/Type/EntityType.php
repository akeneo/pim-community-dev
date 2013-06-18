<?php

namespace Oro\Bundle\EntityExtendBundle\Config\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class EntityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('class_name');
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                //'data_class' => 'Oro\Bundle\EntityConfigBundle\Entity\EntityConfig'
            )
        );
    }

    public function getName()
    {
        return 'oro_bundle_flexibleManagerBundle_configType';
    }
}
{

}
