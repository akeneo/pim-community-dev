<?php

namespace Oro\Bundle\InstallerBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
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
            ->add(
                'username',
                'text',
                array(
                    'label' => 'form.setup.username',
                )
            )
            ->add(
                'plainPassword',
                'repeated',
                array(
                    'type'           => 'password',
                    'invalid_message' => 'The password fields must match.',
                    'first_options'  => array('label' => 'form.setup.password'),
                    'second_options' => array('label' => 'form.setup.password_re'),
                )
            )
            ->add(
                'email',
                'email',
                array(
                    'label' => 'form.setup.email',
                )
            )
            ->add(
                'firstName',
                'text',
                array(
                    'label' => 'form.setup.firstname',
                )
            )
            ->add(
                'lastName',
                'text',
                array(
                    'label' => 'form.setup.lastname',
                )
            )
            ->add(
                'loadFixtures',
                'checkbox',
                array(
                    'label'    => 'form.setup.load_fixtures',
                    'required' => false,
                    'mapped'   => false,
                )
            );
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class'        => $this->dataClass,
                'validation_groups' => array('Registration', 'Default'),
            )
        );
    }

    public function getName()
    {
        return 'oro_installer_setup';
    }
}
