<?php

namespace Oro\Bundle\InstallerBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class ConfigurationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('database', 'oro_configuration_database', array(
                'label' => 'form.configuration.database'
            ))
            ->add('mailer', 'oro_configuration_mailer', array(
                'label' => 'form.configuration.mailer'
            ))
            ->add('locale', 'oro_configuration_locale', array(
                'label' => 'form.configuration.locale'
            ))
            ->add('hidden', 'oro_configuration_hidden')
        ;
    }

    public function getName()
    {
        return 'oro_configuration';
    }
}
