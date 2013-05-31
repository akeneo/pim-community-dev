<?php
namespace Oro\Bundle\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class UserSelectType extends AbstractType
{
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'configs' => array(
                    'placeholder' => 'Choose a user...',
                    'datasource' => 'grid',
                    'route' => 'oro_user_index',
                    'grid' => array(
                        'name' => 'users',
                        'property' => 'username'
                    )
                ),
                'empty_value' => '',
                'empty_data'  => null
            )
        );
    }

    public function getParent()
    {
        return 'genemu_jqueryselect2_hidden';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_user_select';
    }
}
