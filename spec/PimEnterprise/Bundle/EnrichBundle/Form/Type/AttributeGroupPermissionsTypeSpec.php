<?php

namespace spec\PimEnterprise\Bundle\EnrichBundle\Form\Type;

use PhpSpec\ObjectBehavior;
use PimEnterprise\Bundle\SecurityBundle\Form\Type\GroupsType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AttributeGroupPermissionsTypeSpec extends ObjectBehavior
{
    function it_is_a_form_type()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\Form\AbstractType');
    }

    function it_has_a_block_prefix()
    {
        $this->getBlockPrefix()->shouldReturn('pimee_enrich_attribute_group_permissions');
    }

    function it_has_view_and_edit_permission_fields(FormBuilderInterface $builder)
    {
        $this->buildForm($builder, []);

        $builder
            ->add(
                'view',
                GroupsType::class,
                ['label' => 'attribute group.permissions.view.label', 'help' => 'attribute group.permissions.view.help']
            )
            ->shouldHaveBeenCalled();

        $builder
            ->add(
                'edit',
                GroupsType::class,
                ['label' => 'attribute group.permissions.edit.label', 'help' => 'attribute group.permissions.edit.help']
            )
            ->shouldHaveBeenCalled();
    }

    function it_does_not_map_the_fields_to_the_entity_by_default(OptionsResolver $resolver)
    {
        $this->configureOptions($resolver, []);

        $resolver->setDefaults(['mapped' => false])->shouldHaveBeenCalled();
    }
}
