<?php
declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\RuleEngine\Component\ActionApplier\Concatenate;

use Akeneo\Pim\Automation\RuleEngine\Component\ActionApplier\Concatenate\NumberValueStringifier;
use Akeneo\Pim\Automation\RuleEngine\Component\ActionApplier\Concatenate\ValueStringifierInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use PhpSpec\ObjectBehavior;

class NumberValueStringifierSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedWith(['pim_catalog_number']);
    }

    public function it_is_a_number_stringifier(): void
    {
        $this->shouldHaveType(NumberValueStringifier::class);
        $this->shouldImplement(ValueStringifierInterface::class);
    }

    public function it_stringifies_a_number_value(): void
    {
        $numberValue = ScalarValue::value('decimal_attribute', 12.8);

        $this->stringify($numberValue)->shouldReturn('12.8');
    }

    public function it_stringifies_a_decimal_value_without_trailing_zero(): void
    {
        $numberValue = ScalarValue::value('decimal_attribute', 12.0400);

        $this->stringify($numberValue)->shouldReturn('12.04');
    }

    public function it_stringifies_string_decimal_value_without_trailing_zero(): void
    {
        $numberValue = ScalarValue::value('decimal_attribute', '10.0000');

        $this->stringify($numberValue)->shouldReturn('10');
    }
}
