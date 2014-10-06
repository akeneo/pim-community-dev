<?php

namespace spec\Pim\Bundle\CatalogBundle\Validator;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\AttributeOption;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Prophecy\Argument;
use Symfony\Component\Validator\ExecutionContext;

class AttributeValidatorSpec extends ObjectBehavior
{
    function it_validates_attribute_with_unique_options(
        AbstractAttribute $attribute,
        ExecutionContext $context,
        AttributeOption $option1,
        AttributeOption $option2,
        AttributeOption $option3,
        AttributeOption $option4
    ) {
        $option1->getCode()->willReturn('ab');
        $option2->getCode()->willReturn('cd');
        $option3->getCode()->willReturn('ef');
        $option4->getCode()->willReturn('gh');
        $attribute->getOptions()->willReturn([$option1, $option2, $option3, $option4]);

        $context->addViolation(Argument::any())->shouldNotBeCalled();

        $this->areOptionsValid($attribute, $context);
    }

    function it_does_not_validate_attribute_with_non_unique_options(
        AbstractAttribute $attribute,
        ExecutionContext $context,
        AttributeOption $option1,
        AttributeOption $option2,
        AttributeOption $option3,
        AttributeOption $option4
    ) {
        $option1->getCode()->willReturn('ab');
        $option2->getCode()->willReturn('cd');
        $option3->getCode()->willReturn('ef');
        $option4->getCode()->willReturn('ab');
        $attribute->getOptions()->willReturn([$option1, $option2, $option3, $option4]);

        $context->addViolation('Code must be different for each option.')->shouldBeCalled();

        $this->areOptionsValid($attribute, $context);
    }

    function it_does_not_validate_attribute_with_null_options(
        AbstractAttribute $attribute,
        ExecutionContext $context,
        AttributeOption $option1,
        AttributeOption $option2,
        AttributeOption $option3,
        AttributeOption $option4
    ) {
        $option1->getCode()->willReturn('ab');
        $option2->getCode()->willReturn(null);
        $option3->getCode()->willReturn('ef');
        $option4->getCode()->willReturn(null);
        $attribute->getOptions()->willReturn([$option1, $option2, $option3, $option4]);

        $context->addViolation('Code must be specified for all options.')->shouldBeCalled();

        $this->areOptionsValid($attribute, $context);
    }

    function it_validates_attribute_with_unique_similar_options(
        AbstractAttribute $attribute,
        ExecutionContext $context,
        AttributeOption $option1,
        AttributeOption $option2,
        AttributeOption $option3,
        AttributeOption $option4,
        AttributeOption $option5,
        AttributeOption $option6
    ) {
        $option1->getCode()->willReturn('00');
        $option2->getCode()->willReturn('0');
        $option3->getCode()->willReturn('0ab');
        $option4->getCode()->willReturn(' 0aB');
        $option5->getCode()->willReturn('0l');
        $option6->getCode()->willReturn('0ef');
        $attribute->getOptions()->willReturn([$option1, $option2, $option3, $option4, $option5, $option6]);

        $context->addViolation(Argument::any())->shouldNotBeCalled();

        $this->areOptionsValid($attribute, $context);
    }

    function it_does_not_validate_attribute_with_non_string_options(
        AbstractAttribute $attribute,
        ExecutionContext $context,
        AttributeOption $option1,
        AttributeOption $option2,
        AttributeOption $option3,
        AttributeOption $option4
    ) {
        $option1->getCode()->willReturn('ab');
        $option2->getCode()->willReturn(0);
        $option3->getCode()->willReturn('ef');
        $option4->getCode()->willReturn('gh');
        $attribute->getOptions()->willReturn([$option1, $option2, $option3, $option4]);

        $context->addViolation('Code must be a string. Type "integer" found.')->shouldBeCalled();

        $this->areOptionsValid($attribute, $context);
    }
}
