<?php

namespace Oro\Bundle\InstallerBundle\Form\Type\Configuration;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

class MailerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('oro_mailer_transport', 'choice', array(
                'choices' => array(
                    'smtp'     => 'form.configuration.mailer.transport.smtp',
                    'gmail'    => 'form.configuration.mailer.transport.gmail',
                    'mail'     => 'form.configuration.mailer.transport.mail',
                    'sendmail' => 'form.configuration.mailer.transport.sendmail',
                ),
                'constraints' => array(
                    new Assert\NotBlank(),
                ),
                'label' => 'form.configuration.mailer.transport',
            ))
            ->add('oro_mailer_host', 'text', array(
                'data'  => '127.0.0.1',
                'constraints' => array(
                    new Assert\NotBlank(),
                ),
                'label' => 'form.configuration.mailer.host',
            ))
            ->add('oro_mailer_user', 'text', array(
                'label'    => 'form.configuration.mailer.user',
                'required' => false,
            ))
            ->add('oro_mailer_password', 'password', array(
                'required' => false,
                'label'    => 'form.configuration.mailer.password'
            ))
        ;
    }

    public function getName()
    {
        return 'oro_configuration_mailer';
    }
}
