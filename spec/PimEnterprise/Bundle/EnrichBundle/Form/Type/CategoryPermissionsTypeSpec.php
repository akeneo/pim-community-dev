<?php

namespace spec\PimEnterprise\Bundle\EnrichBundle\Form\Type;

use PhpSpec\ObjectBehavior;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CategoryPermissionsTypeSpec extends ObjectBehavior
{
    function it_is_a_form_type()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\Form\AbstractType');
    }

    function it_has_a_name()
    {
        $this->getName()->shouldReturn('pimee_enrich_category_permissions');
    }

    function it_has_view_edit_and_own_permission_fields(FormBuilderInterface $builder)
    {
        $this->buildForm($builder, []);

        $builder
            ->add(
                'view',
                'pimee_security_groups',
                ['label' => 'category.permissions.view.label', 'help' => 'category.permissions.view.help']
            )
            ->shouldHaveBeenCalled();

        $builder
            ->add(
                'edit',
                'pimee_security_groups',
                ['label' => 'category.permissions.edit.label', 'help' => 'category.permissions.edit.help']
            )
            ->shouldHaveBeenCalled();

        $builder
            ->add(
                'own',
                'pimee_security_groups',
                ['label' => 'category.permissions.own.label', 'help' => 'category.permissions.own.help']
            )->shouldHaveBeenCalled();
    }

    function it_has_a_field_for_applying_the_permissions_on_children(FormBuilderInterface $builder)
    {
        $this->buildForm($builder, []);

        $builder
            ->add(
                'apply_on_children',
                'checkbox',
                [
                    'label' => 'category.permissions.apply_on_children.label',
                    'help'  => 'category.permissions.apply_on_children.help',
                    'data' => true,
                    'required' => false
                ]
            )->shouldHaveBeenCalled();
    }

    function it_does_not_map_the_fields_to_the_entity_by_default(OptionsResolver $resolver)
    {
        $this->setDefaultOptions($resolver, []);

        $resolver->setDefaults(['mapped' => false])->shouldHaveBeenCalled();
    }
}
