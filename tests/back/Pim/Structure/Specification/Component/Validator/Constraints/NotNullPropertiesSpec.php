<?php

namespace Specification\Akeneo\Pim\Structure\Component\Validator\Constraints;

use Akeneo\Pim\Structure\Component\Validator\Constraints\NotNullProperties;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Validator\Constraint;

class NotNullPropertiesSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(['properties' => ['my_property']]);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(NotNullProperties::class);
    }

    function it_is_a_validator_constraint()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\Validator\Constraint');
    }

    function it_has_message()
    {
        $this->message->shouldBeEqualTo('This value should not be blank.');
    }

    function it_has_class_target()
    {
        $this->getTargets()->shouldBe(Constraint::CLASS_CONSTRAINT);
    }

    function it_has_default_option_equal_to_properties()
    {
        $this->getDefaultOption()->shouldBe('properties');
    }

    function it_has_required_options_contain_properties()
    {
        $this->getRequiredOptions()->shouldBe(['properties']);
    }
}
