<?php

namespace Oro\Bundle\NotificationBundle\Form\Type;

use Doctrine\ORM\EntityRepository;

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
        $builder->add(
            'event',
            'entity',
            array(
                'class' => 'OroNotificationBundle:Event',
                'property' => 'name',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('c')
                        ->orderBy('c.name', 'ASC');
                },
                'empty_value' => '',
                'empty_data'  => null
            )
        );
        $builder->add(
            'entityName',
            'choice',
            array(
                'choices' => array(
                    'test',
                ),
                'multiple' => false,
            )
        );
        $builder->add(
            'template',
            'choice',
            array(
                'choices' => array(
                    '@testTemplate',
                ),
                'multiple' => false,
            )
        );
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
