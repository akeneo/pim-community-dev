<?php
declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints;

use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\NotDecimal;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Validator\Constraint;

class NotDecimalSpec extends ObjectBehavior
{
    function it_is_initializable(): void
    {
        $this->shouldHaveType(NotDecimal::class);
    }

    function it_is_a_validator_constraint(): void
    {
        $this->shouldBeAnInstanceOf(Constraint::class);
    }

    function it_has_message()
    {
        $this->message->shouldBe('The %attribute% attribute requires a non-decimal value, and %value% is not a valid value.');
    }

    function it_provides_an_attribute_code(): void
    {
        $this->beConstructedWith(['attributeCode' => 'a_code']);
        $this->attributeCode->shouldBe('a_code');
    }

    function it_provides_an_empty_string_if_there_is_no_attribute_code(): void
    {
        $this->attributeCode->shouldBe('');
    }
}
