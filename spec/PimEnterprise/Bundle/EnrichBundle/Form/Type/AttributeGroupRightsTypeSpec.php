<?php

namespace spec\PimEnterprise\Bundle\EnrichBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use PhpSpec\ObjectBehavior;

class AttributeGroupRightsTypeSpec extends ObjectBehavior
{
    function it_is_a_form_type()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\Form\AbstractType');
    }

    function it_has_a_name()
    {
        $this->getName()->shouldReturn('pimee_enrich_attribute_group_rights');
    }

    function it_has_read_and_write_permission_fields(FormBuilderInterface $builder)
    {
        $this->buildForm($builder, []);

        $builder
            ->add('read', 'pimee_security_roles', ['label' => 'attribute group.rights.read.label'])
            ->shouldHaveBeenCalled();

        $builder
            ->add('write', 'pimee_security_roles', ['label' => 'attribute group.rights.write.label'])
            ->shouldHaveBeenCalled();
    }

    function it_does_not_map_the_fields_to_the_entity_by_default(OptionsResolverInterface $resolver)
    {
        $this->setDefaultOptions($resolver, []);

        $resolver->setDefaults(['mapped' => false])->shouldHaveBeenCalled();
    }
}
