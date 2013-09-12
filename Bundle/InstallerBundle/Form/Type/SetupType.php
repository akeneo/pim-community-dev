<?php

namespace Oro\Bundle\InstallerBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SetupType extends AbstractType
{
    protected $dataClass;

    public function __construct($dataClass)
    {
        $this->dataClass = $dataClass;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', 'text', array(
                'constraints' => array(
                    new Assert\NotBlank(),
                ),
                'label' => 'form.setup.username'
            ))
            ->add('plain_password', 'password', array(
                'constraints' => array(
                    new Assert\NotBlank(),
                ),
                'label' => 'form.setup.plain_password'
            ))
            ->add('email', 'email', array(
                'constraints' => array(
                    new Assert\NotBlank(),
                    new Assert\Email(),
                ),
                'label' => 'form.setup.email'
            ))
            ->add('load_fixtures', 'checkbox', array(
                'required' => false,
                'mapped'   => false,
                'label'    => 'form.setup.load_fixtures'
            ))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setDefaults(array(
                'data_class' => $this->dataClass
            ))
        ;
    }

    public function getName()
    {
        return 'oro_setup';
    }
}
