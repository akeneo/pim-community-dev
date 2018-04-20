<?php

namespace spec\PimEnterprise\Bundle\SecurityBundle\Form\Type;

use PhpSpec\ObjectBehavior;
use PimEnterprise\Bundle\SecurityBundle\Form\Type\GroupsType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class JobProfilePermissionsTypeSpec extends ObjectBehavior
{
    function it_is_a_form_type()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\Form\AbstractType');
    }

    function it_has_a_block_prefix()
    {
        $this->getBlockPrefix()->shouldReturn('pimee_import_export_job_profile_permissions');
    }

    function it_has_view_and_edit_permission_fields(FormBuilderInterface $builder)
    {
        $this->buildForm($builder, []);

        $builder
            ->add('execute', GroupsType::class, ['label' => 'job_profile.permissions.execute.label'])
            ->shouldHaveBeenCalled();

        $builder
            ->add('edit', GroupsType::class, ['label' => 'job_profile.permissions.edit.label'])
            ->shouldHaveBeenCalled();
    }

    function it_does_not_map_the_fields_to_the_entity_by_default(OptionsResolver $resolver)
    {
        $this->configureOptions($resolver, []);

        $resolver->setDefaults(['mapped' => false])->shouldHaveBeenCalled();
    }
}
