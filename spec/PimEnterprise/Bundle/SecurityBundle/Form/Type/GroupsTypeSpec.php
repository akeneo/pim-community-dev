<?php

namespace spec\PimEnterprise\Bundle\SecurityBundle\Form\Type;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GroupsTypeSpec extends ObjectBehavior
{
    function it_is_a_form_type()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\Form\AbstractType');
    }

    function it_has_a_name()
    {
        $this->getName()->shouldReturn('pimee_security_groups');
    }

    function it_extends_the_entity_form_type()
    {
        $this->getParent()->shouldReturn('entity');
    }

    function it_configures_the_form_type_to_provide_available_user_groups(OptionsResolver $resolver)
    {
        $this->setDefaultOptions($resolver, []);

        $resolver
            ->setDefaults(
                Argument::allOf(
                    Argument::withEntry('class', 'OroUserBundle:Group'),
                    Argument::withEntry('property', 'name'),
                    Argument::withEntry('multiple', true),
                    Argument::withEntry('required', false),
                    Argument::withEntry('select2', true)
                )
            )
            ->shouldHaveBeenCalled();
    }
}
