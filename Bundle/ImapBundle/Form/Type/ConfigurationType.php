<?php

namespace Oro\Bundle\ImapBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ConfigurationType extends AbstractType
{
    const NAME = 'oro_imap_configuration';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            function (FormEvent $event) {
                $data = $event->getData();
                if (empty($data['password'])) {
                    $event->getForm()->remove('password');

                    unset($data['password']);
                    $event->setData($data);
                }
            }
        );


        $builder
            ->add('host', 'text', array('required' => true))
            ->add('port', 'text', array('required' => true))
            ->add(
                'ssl',
                'choice',
                array(
                    'choices'     => array('ssl' => 'ssl', 'tsl' => 'tsl'),
                    'empty_data'  => null,
                    'empty_value' => '',
                    'required'    => false
                )
            )
            ->add('user', 'text', array('required' => true))
            ->add('password', 'password', array('required' => true));
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class'     => 'Oro\\Bundle\\ImapBundle\\Entity\\ImapEmailOrigin',
                'error_bubbling' => true
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }
}
