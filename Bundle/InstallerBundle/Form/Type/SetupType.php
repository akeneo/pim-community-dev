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
            ->add(
                'username',
                'text',
                array(
                    'label'         => 'form.setup.username',
                    'constraints'   => array(
                        new Assert\NotBlank(),
                        new Assert\Length(array('min' => 3, 'max' => 255)),
                    ),
                )
            )
            ->add(
                'plainPassword',
                'repeated',
                array(
                    'type'           => 'password',
                    'first_options'  => array('label' => 'form.setup.password'),
                    'second_options' => array('label' => 'form.setup.password_re'),
                    'constraints'    => array(
                        new Assert\NotBlank(),
                    ),
                )
            )
            ->add(
                'email',
                'email',
                array(
                    'label'         => 'form.setup.email',
                    'constraints'   => array(
                        new Assert\NotBlank(),
                        new Assert\Email(),
                    ),
                )
            )
            ->add(
                'load_fixtures',
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
                'data_class' => $this->dataClass
            )
        );
    }

    public function getName()
    {
        return 'oro_installer_setup';
    }
}
