<?php

namespace Oro\Bundle\InstallerBundle\Form\Type\Configuration;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class MailerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'oro_installer_mailer_transport',
                'choice',
                array(
                    'label'         => 'form.configuration.mailer.transport',
                    'preferred_choices' => array('mail'),
                    'choices'       => array(
                        'smtp'      => 'SMTP',
                        'gmail'     => 'Gmail',
                        'mail'      => 'PHP mail',
                        'sendmail'  => 'sendmail',
                    ),
                    'constraints'   => array(
                        new Assert\NotBlank(),
                    ),
                )
            )
            ->add(
                'oro_installer_mailer_host',
                'text',
                array(
                    'label'         => 'form.configuration.mailer.host',
                    'constraints'   => array(
                        new Assert\NotBlank(),
                    ),
                )
            )
            ->add(
                'oro_installer_mailer_user',
                'text',
                array(
                    'label'         => 'form.configuration.mailer.user',
                    'required'      => false,
                )
            )
            ->add(
                'oro_installer_mailer_password',
                'password',
                array(
                    'label'         => 'form.configuration.mailer.password',
                    'required'      => false,
                )
            );
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'oro_installer_mailer_host' => '127.0.0.1',
            )
        );
    }

    public function getName()
    {
        return 'oro_installer_configuration_mailer';
    }
}
