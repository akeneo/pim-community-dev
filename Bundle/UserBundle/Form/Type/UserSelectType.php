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
                    'datasource' => 'user',
                    'placeholder' => 'Choose a user...',
                    'route' => 'oro_user_autocomplete',
                    'properties' => array('first_name', 'last_name')
                ),
                'entity_class' => 'Oro\Bundle\UserBundle\Entity\User'
                //'autocomplete_alias' => 'users'
            )
        );
    }

    public function getParent()
    {
        return 'oro_jqueryselect2_hidden';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_user_select';
    }
}
