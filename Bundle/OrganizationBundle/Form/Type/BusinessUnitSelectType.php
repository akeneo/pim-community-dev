<?php
namespace Oro\Bundle\OrganizationBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class BusinessUnitSelectType extends AbstractType
{
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'configs' => array(
                    'width' => '400px',
                    'placeholder' => 'oro.organization.form.choose_business_user',
                    'result_template_twig' => 'OroOrganizationBundle:Js:businessUnitResult.html.twig',
                    'selection_template_twig' => 'OroOrganizationBundle:Js:businessUnitSelection.html.twig'
                ),
                'autocomplete_alias' => 'business_unit'
            )
        );
    }

    public function getParent()
    {
        return 'oro_jqueryselect2_hidden';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_business_unit_select';
    }
}
