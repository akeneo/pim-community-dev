<?php

namespace spec\PimEnterprise\Bundle\EnrichBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use PimEnterprise\Bundle\EnrichBundle\Form\Subscriber\AttributeGroupRightsSubscriber;

class AttributeGroupTypeSpec extends ObjectBehavior
{
    function let(AttributeGroupRightsSubscriber $subscriber)
    {
        $this->beConstructedWith($subscriber);
    }

    function it_is_a_form_type()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\Form\AbstractType');
    }

    function it_adds_the_rights_field_and_related_subscriber_to_the_form(FormBuilderInterface $builder, $subscriber)
    {
        $builder->add(Argument::cetera())->willReturn($builder);
        $builder->addEventSubscriber(Argument::any())->willReturn($builder);

        $this->buildForm($builder, []);

        $builder
            ->add('rights', 'pimee_enrich_attribute_group_rights')
            ->shouldHaveBeenCalled();

        $builder->addEventSubscriber($subscriber)->shouldHaveBeenCalled();
    }
}
