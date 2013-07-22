<?php

namespace Oro\Bundle\OrganizationBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

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
                    'required' => true,
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
                    'required' => true,
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
                'parent',
                'entity',
                array(
                    'label'    => 'Parent Unit',
                    'class'    => 'OroOrganizationBundle:BusinessUnit',
                    'property' => 'name',
                    'required' => false,
                    'multiple' => false,
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
            );
        // tags
        $builder->add(
            'tags',
            'oro_tag_select'
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
