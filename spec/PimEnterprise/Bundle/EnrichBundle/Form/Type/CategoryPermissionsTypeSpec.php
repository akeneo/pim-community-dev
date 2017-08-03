<?php

namespace spec\PimEnterprise\Bundle\EnrichBundle\Form\Type;

use PhpSpec\ObjectBehavior;
use PimEnterprise\Bundle\SecurityBundle\Form\Type\GroupsType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CategoryPermissionsTypeSpec extends ObjectBehavior
{
    function it_is_a_form_type()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\Form\AbstractType');
    }

    function it_has_a_block_prefix()
    {
        $this->getBlockPrefix()->shouldReturn('pimee_enrich_category_permissions');
    }

    function it_has_view_edit_and_own_permission_fields(FormBuilderInterface $builder)
    {
        $this->buildForm($builder, []);

        $builder
            ->add(
                'view',
                GroupsType::class,
                ['label' => 'category.permissions.view.label', 'help' => 'category.permissions.view.help']
            )
            ->shouldHaveBeenCalled();

        $builder
            ->add(
                'edit',
                GroupsType::class,
                ['label' => 'category.permissions.edit.label', 'help' => 'category.permissions.edit.help']
            )
            ->shouldHaveBeenCalled();

        $builder
            ->add(
                'own',
                GroupsType::class,
                ['label' => 'category.permissions.own.label', 'help' => 'category.permissions.own.help']
            )->shouldHaveBeenCalled();
    }

    function it_has_a_field_for_applying_the_permissions_on_children(FormBuilderInterface $builder)
    {
        $this->buildForm($builder, []);

        $builder
            ->add(
                'apply_on_children',
                CheckboxType::class,
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
        $this->configureOptions($resolver, []);

        $resolver->setDefaults(['mapped' => false])->shouldHaveBeenCalled();
    }
}
