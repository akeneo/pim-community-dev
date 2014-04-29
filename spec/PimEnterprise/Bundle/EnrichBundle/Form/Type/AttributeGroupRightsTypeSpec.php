<?php

namespace spec\PimEnterprise\Bundle\EnrichBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use PhpSpec\ObjectBehavior;

class AttributeGroupRightsTypeSpec extends ObjectBehavior
{
    public function it_is_a_form_type()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\Form\AbstractType');
    }

    public function it_has_a_name()
    {
        $this->getName()->shouldReturn('pim_enterprise_enrich_attribute_group_rights');
    }

    public function it_has_read_and_write_permission_fields(FormBuilderInterface $builder)
    {
        $this->buildForm($builder, []);

        $builder
            ->add('read', 'pim_enterprise_security_roles', ['label' => 'attribute group.rights.read.label'])
            ->shouldHaveBeenCalled();

        $builder
            ->add('write', 'pim_enterprise_security_roles', ['label' => 'attribute group.rights.write.label'])
            ->shouldHaveBeenCalled();
    }

    public function it_does_not_map_the_fields_to_the_entity_by_default(OptionsResolverInterface $resolver)
    {
        $this->setDefaultOptions($resolver, []);

        $resolver->setDefaults(['mapped' => false])->shouldHaveBeenCalled();
    }
}
