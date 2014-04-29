<?php

namespace spec\PimEnterprise\Bundle\EnrichBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class AttributeGroupTypeSpec extends ObjectBehavior
{
    function it_is_a_form_type()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\Form\AbstractType');
    }

    function it_adds_the_rights_field_to_the_form(FormBuilderInterface $builder)
    {
        $builder->add(Argument::cetera())->willReturn($builder);
        $builder->addEventSubscriber(Argument::any())->willReturn($builder);

        $this->buildForm($builder, []);

        $builder
            ->add('rights', 'pim_enterprise_enrich_attribute_group_rights')
            ->shouldHaveBeenCalled();
    }
}
