<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Value;

use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\NumberValue;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use PhpSpec\ObjectBehavior;

class NumberValueSpec extends ObjectBehavior
{
    public function it_is_initializable(): void
    {
        $this->shouldImplement(ValueInterface::class);
        $this->shouldHaveType(NumberValue::class);
    }

    public function it_equals_other_number_values(): void
    {
        $this->beConstructedThrough('scopableLocalizableValue', ['number', 10.356, 'ecommerce', 'en_US']);

        $this->isEqual(NumberValue::scopableLocalizableValue('number', 10.356, 'ecommerce', 'en_US'))->shouldBe(true);
        $this->isEqual(NumberValue::scopableLocalizableValue('number', '10.356', 'ecommerce', 'en_US'))->shouldBe(true);
        $this->isEqual(NumberValue::scopableLocalizableValue('number', '0010.35600', 'ecommerce', 'en_US'))->shouldBe(true);
        $this->isEqual(NumberValue::scopableLocalizableValue('number', 1.0356E1, 'ecommerce', 'en_US'))->shouldBe(true);
        $this->isEqual(NumberValue::scopableLocalizableValue('number', '1.0356E1', 'ecommerce', 'en_US'))->shouldBe(true);
    }

    public function it_equals_other_number_values_with_big_numbers(): void
    {
        $this->beConstructedThrough('value', ['number', '1234567890.09876543212345']);
        $this->isEqual(NumberValue::value('number', '1234567890.09876543212345'))->shouldBe(true);
        $this->isEqual(NumberValue::value('number', '001234567890.0987654321234500'))->shouldBe(true);
        $this->isEqual(NumberValue::value('number', '1.23456789009876543212345E9'))->shouldBe(true);
        $this->isEqual(NumberValue::value('number', 1234567890.09876543212345))->shouldBe(true);
    }

    public function it_does_not_equal_number_values_with_different_data(): void
    {
        $this->beConstructedThrough('scopableLocalizableValue', ['number', 10.356, 'ecommerce', 'en_US']);

        $this->isEqual(NumberValue::scopableLocalizableValue('number', 10.357, 'ecommerce', 'en_US'))->shouldBe(false);
        $this->isEqual(NumberValue::scopableLocalizableValue('number', 10.35600005, 'ecommerce', 'en_US'))->shouldBe(false);
        $this->isEqual(NumberValue::scopableLocalizableValue('number', '11.356', 'ecommerce', 'en_US'))->shouldBe(false);
        $this->isEqual(NumberValue::scopableLocalizableValue('number', '0011.35600', 'ecommerce', 'en_US'))->shouldBe(false);
        $this->isEqual(NumberValue::scopableLocalizableValue('number', '1.1356E1', 'ecommerce', 'en_US'))->shouldBe(false);
        $this->isEqual(NumberValue::scopableLocalizableValue('number', 10, 'ecommerce', 'en_US'))->shouldBe(false);
        $this->isEqual(NumberValue::scopableLocalizableValue('number', 'abc', 'ecommerce', 'en_US'))->shouldBe(false);
        $this->isEqual(NumberValue::scopableLocalizableValue('number', true, 'ecommerce', 'en_US'))->shouldBe(false);
        $this->isEqual(NumberValue::scopableLocalizableValue('number', 'A10.356', 'ecommerce', 'en_US'))->shouldBe(false);
    }

    public function it_does_not_equal_non_number_values(): void
    {
        $this->beConstructedThrough('scopableLocalizableValue', ['number', 10.356, 'ecommerce', 'en_US']);

        $this->isEqual(ScalarValue::scopableLocalizableValue('number', 10.356, 'ecommerce', 'en_US'))->shouldBe(false);
    }

    public function it_does_not_equal_value_with_different_scope(): void
    {
        $this->beConstructedThrough('scopableLocalizableValue', ['number', 10.356, 'ecommerce', 'en_US']);

        $this->isEqual(NumberValue::scopableLocalizableValue('number', 10.356, 'print', 'en_US'))->shouldBe(false);
        $this->isEqual(NumberValue::localizableValue('number', 10.356, 'en_US'))->shouldBe(false);
    }

    public function it_does_not_equal_value_with_different_locale(): void
    {
        $this->beConstructedThrough('scopableLocalizableValue', ['number', 10.356, 'ecommerce', 'en_US']);

        $this->isEqual(NumberValue::scopableLocalizableValue('number', 10.356, 'ecommerce', 'fr_FR'))->shouldBe(false);
        $this->isEqual(NumberValue::scopableValue('number', 10.356, 'ecommerce'))->shouldBe(false);
    }
}
