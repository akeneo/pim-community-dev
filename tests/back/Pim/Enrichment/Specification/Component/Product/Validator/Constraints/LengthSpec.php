<?php
declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints;

use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\Length;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Length as BaseLength;

class LengthSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedWith(['max' => 5]);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(Length::class);
    }

    function it_is_a_validator_constraint()
    {
        $this->shouldBeAnInstanceOf(Constraint::class);
        $this->shouldBeAnInstanceOf(BaseLength::class);
    }

    function it_has_a_max_message()
    {
        $this->maxMessage->shouldBe('The %attribute% attribute must not contain more than %limit% characters. The submitted value is too long.');
    }

    function it_provides_an_attribute_code()
    {
        $this->beConstructedWith(['max' => 5, 'attributeCode' => 'a_code']);

        $this->attributeCode->shouldBe('a_code');
    }

    function it_provides_empty_string_if_there_is_no_attribute_code()
    {
        $this->attributeCode->shouldBe('');
    }
}
