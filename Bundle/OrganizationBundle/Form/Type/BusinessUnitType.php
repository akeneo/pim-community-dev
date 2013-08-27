<?php

namespace Oro\Bundle\OrganizationBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Doctrine\ORM\EntityRepository;

class BusinessUnitType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'name',
                'text',
                array(
                    'required' => true,
                )
            )
            ->add(
                'phone',
                'text',
                array(
                    'required' => false,
                )
            )
            ->add(
                'website',
                'text',
                array(
                    'required' => false,
                )
            )
            ->add(
                'email',
                'text',
                array(
                    'required' => false,
                )
            )
            ->add(
                'fax',
                'text',
                array(
                    'required' => false,
                )
            )
            ->add(
                'organization',
                'entity',
                array(
                    'label'    => 'Organization',
                    'class'    => 'OroOrganizationBundle:Organization',
                    'property' => 'name',
                    'required' => true,
                    'multiple' => false,
                )
            )
            ->add(
                'appendUsers',
                'oro_entity_identifier',
                array(
                    'class'    => 'OroUserBundle:User',
                    'required' => false,
                    'mapped'   => false,
                    'multiple' => true,
                )
            )
            ->add(
                'removeUsers',
                'oro_entity_identifier',
                array(
                    'class'    => 'OroUserBundle:User',
                    'required' => false,
                    'mapped'   => false,
                    'multiple' => true,
                )
            );
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Oro\Bundle\OrganizationBundle\Entity\BusinessUnit'
            )
        );
    }

    public function getName()
    {
        return 'oro_business_unit';
    }
}
