<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints;

use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\Range;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Validator\Constraint;

class RangeSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(Range::class);
    }

    function it_is_a_validator_constraint()
    {
        $this->shouldBeAnInstanceOf(Constraint::class);
    }

    function it_has_a_min_date_message()
    {
        $this->beConstructedWith(['min' => new \DateTime()]);
        $this->minDateMessage->shouldBe('The {{ attribute_code }} attribute requires a date that should be {{ limit }} or after.');
    }

    function it_has_a_max_date_message()
    {
        $this->beConstructedWith(['max' => new \DateTime()]);
        $this->maxDateMessage->shouldBe('The {{ attribute_code }} attribute requires a date that should be {{ limit }} or before.');
    }

    function it_has_an_invalid_number_message()
    {
        $this->beConstructedWith(['max' => new \DateTime()]);
        $this->invalidMessage->shouldBe('The {{ attribute }} attribute requires a number, and the submitted {{ value }} value is not.');
    }

    function it_has_a_min_message()
    {
         $this->beConstructedWith(['min' => 1]);
        $this->minMessage->shouldBe('The %attribute% attribute requires an equal or greater than %min_value% value.');
    }

    function it_has_a_max_message()
    {
        $this->beConstructedWith(['max' => 100]);
        $this->maxMessage->shouldBe('The %attribute% attribute requires an equal or lesser than %max_value% value.');
    }

    function it_has_an_attribute_code()
    {
        $this->beConstructedWith(['min' => new \DateTime(), 'attributeCode' => 'a_code']);
        $this->attributeCode->shouldBe('a_code');
    }

    function it_provides_an_empty_string_if_no_attribute_code_is_specified()
    {
        $this->beConstructedWith(['min' => new \DateTime()]);
        $this->attributeCode->shouldBe('');
    }
}
