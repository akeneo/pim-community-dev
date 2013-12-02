<?php

namespace Oro\Bundle\InstallerBundle\Form\Type\Configuration;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
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
                        'mail'      => 'PHP mail',
                        'smtp'      => 'SMTP',
                        'sendmail'  => 'sendmail',
                    ),
                    'constraints'   => array(
                        new Assert\NotBlank(),
                    ),
                   'client_validation' => false,
                )
            )
            ->add(
                'oro_installer_mailer_host',
                'text',
                array(
                    'label'         => 'form.configuration.mailer.host',
                    'constraints'   => array(
                        new Assert\NotBlank(array('groups' => array('SMTP'))),
                    ),
                )
            )
            ->add(
                'oro_installer_mailer_port',
                'integer',
                array(
                    'label'         => 'form.configuration.mailer.port',
                    'required'      => false,
                    'constraints'   => array(
                        new Assert\Type(array('groups' => array('SMTP'), 'type' => 'integer')),
                    ),
                )
            )
            ->add(
                'oro_installer_mailer_encryption',
                'choice',
                array(
                    'label'         => 'form.configuration.mailer.encryption',
                    'required'      => false,
                    'preferred_choices' => array(''),
                    'choices'       => array(
                        ''          => 'None',
                        'ssl'       => 'SSL',
                        'tls'       => 'TLS',
                    ),
                    'client_validation' => false,
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
        $resolver->setDefaults(array(
            'validation_groups' => function (FormInterface $form) {
                $data = $form->getData();

                return 'smtp' == $data['oro_installer_mailer_transport']
                    ? array('Default', 'SMTP')
                    : array('Default');
            },
        ));
    }

    public function getName()
    {
        return 'oro_installer_configuration_mailer';
    }
}
