<?php

namespace Oro\Bundle\InstallerBundle\Form\Type\Configuration;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

class HiddenType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('oro_cache', 'hidden', array(
                'data' => 'file_system',
                'constraints' => array(
                    new Assert\NotBlank(),
                )
            ))
            ->add('oro_secret', 'hidden', array(
                'data' => uniqid(),
                'constraints' => array(
                    new Assert\NotBlank(),
                )
            ))
        ;
    }

    public function getName()
    {
        return 'oro_configuration_hidden';
    }
}
