<?php
declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Factory\Value;

use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidAttributeException;
use Akeneo\Pim\Enrichment\Component\Product\Factory\Value\ValidateAttribute;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use PhpSpec\ObjectBehavior;

final class ValidateAttributeSpec extends ObjectBehavior
{
    public function it_is_a_validator_of_the_attribute()
    {
        $this->shouldBeAnInstanceOf(ValidateAttribute::class);
    }

    public function it_is_valid_when_attribute_is_localizable_and_scopable_with_provided_locale_code_and_channel_code()
    {
        $this->shouldNotThrow(InvalidAttributeException::class)->during('validate', [
            $this->getAttribute(true, true),
            'ecommerce',
            'en_US'
        ]);
    }

    public function it_is_valid_when_attribute_is_localizable_with_provided_locale_code_and_null_channel_code()
    {
        $this->shouldNotThrow(InvalidAttributeException::class)->during('validate', [
            $this->getAttribute(false, true),
            null,
            'en_US'
        ]);
    }

    public function it_is_valid_when_attribute_is_scopable_with_null_locale_code_and_provided_channel_code()
    {
        $this->shouldNotThrow(InvalidAttributeException::class)->during('validate', [
            $this->getAttribute(false, true),
            null,
            'en_US'
        ]);
    }

    public function it_is_valid_when_attribute_is_neither_scopable_nor_localizable_with_null_locale_code_and_null_channel_code()
    {
        $this->shouldNotThrow(InvalidAttributeException::class)->during('validate', [
            $this->getAttribute(false, false),
            null,
            null
        ]);
    }


    public function it_throws_an_exception_when_attribute_is_localizable_and_scopable_with_null_locale_code()
    {
        $this->shouldThrow(InvalidAttributeException::class)->during('validate', [
            $this->getAttribute(true, true),
            'ecommerce',
            null
        ]);
    }

    public function it_throws_an_exception_when_attribute_is_localizable_and_scopable_with_null_channel_code()
    {
        $this->shouldThrow(InvalidAttributeException::class)->during('validate', [
            $this->getAttribute(true, true),
            null,
            'en_US'
        ]);
    }

    public function it_throws_an_exception_when_attribute_is_localizable_with_null_locale_code()
    {
        $this->shouldThrow(InvalidAttributeException::class)->during('validate', [
            $this->getAttribute(false, true),
            null,
            null
        ]);
    }

    public function it_throws_an_exception_when_attribute_is_localizable_with_provided_channel_code()
    {
        $this->shouldThrow(InvalidAttributeException::class)->during('validate', [
            $this->getAttribute(false, true),
            'ecommerce',
            'en_US'
        ]);
    }

    public function it_throws_an_exception_when_attribute_is_scopable_with_provided_locale_code()
    {
        $this->shouldThrow(InvalidAttributeException::class)->during('validate', [
            $this->getAttribute(true, false),
            'ecommerce',
            'en_US'
        ]);
    }

    public function it_throws_an_exception_when_attribute_is_scopable_with_null_channel_code()
    {
        $this->shouldThrow(InvalidAttributeException::class)->during('validate', [
            $this->getAttribute(true, false),
            null,
            'en_US'
        ]);
    }

    public function it_throws_an_exception_when_attribute_is_neither_scopable_nor_localizable_with_provided_channel_code()
    {
        $this->shouldThrow(InvalidAttributeException::class)->during('validate', [
            $this->getAttribute(false, false),
            'ecommerce',
            null
        ]);
    }

    public function it_throws_an_exception_when_attribute_is_neither_scopable_nor_localizable_with_provided_locale_code()
    {
        $this->shouldThrow(InvalidAttributeException::class)->during('validate', [
            $this->getAttribute(false, false),
            null,
            'en_US'
        ]);
    }

    private function getAttribute(bool $isScopable, bool $isLocalizable): Attribute
    {
        return new Attribute('an_attribute', AttributeTypes::BOOLEAN, [], $isLocalizable, $isScopable, null, false, 'boolean', []);
    }
}
