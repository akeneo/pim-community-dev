<?php

namespace Oro\Bundle\NotificationBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class EmailNotificationType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Oro\Bundle\NotificationBundle\Entity\EmailNotification',
                'intention'  => 'emailnotification'
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'emailnotification';
    }
}
