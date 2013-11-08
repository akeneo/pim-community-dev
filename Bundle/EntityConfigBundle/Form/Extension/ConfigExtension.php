<?php

namespace Oro\Bundle\EntityConfigBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ConfigExtension extends AbstractTypeExtension
{
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setOptional(array('config_id'));
        $resolver->setAllowedTypes(
            array('config_id' => array(
                'Oro\Bundle\EntityConfigBundle\Config\Id\EntityConfigId',
                'Oro\Bundle\EntityConfigBundle\Config\Id\FieldConfigId'
            )
            )
        );
    }

    public function getExtendedType()
    {
        return 'form';
    }
}
