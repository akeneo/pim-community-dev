<?php

namespace Oro\Bundle\InstallerBundle\Form\Type\Configuration;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

class LocaleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('oro_locale', 'locale', array(
                'preferred_choices' => array('en', 'pl', 'es', 'de'),
                'constraints' => array(
                    new Assert\NotBlank(),
                    new Assert\Locale(),
                ),
                'label' => 'form.configuration.locale.locale',
            ))
        ;
    }

    public function getName()
    {
        return 'oro_configuration_locale';
    }
}
