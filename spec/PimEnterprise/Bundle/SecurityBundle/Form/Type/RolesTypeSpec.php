<?php

namespace spec\PimEnterprise\Bundle\SecurityBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class RolesTypeSpec extends ObjectBehavior
{
    function it_is_a_form_type()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\Form\AbstractType');
    }

    function it_has_a_name()
    {
        $this->getName()->shouldReturn('pimee_security_roles');
    }

    function it_extends_the_entity_form_type()
    {
        $this->getParent()->shouldReturn('entity');
    }

    function it_configures_the_form_type_to_provide_available_roles(OptionsResolverInterface $resolver)
    {
        $this->setDefaultOptions($resolver, []);

        $resolver
            ->setDefaults(
                Argument::allOf(
                    Argument::withEntry('class', 'OroUserBundle:Role'),
                    Argument::withEntry('property', 'label'),
                    Argument::withKey('query_builder'),
                    Argument::withEntry('multiple', true),
                    Argument::withEntry('required', false),
                    Argument::withEntry('select2', true)
                )
            )
            ->shouldHaveBeenCalled();
    }
}
