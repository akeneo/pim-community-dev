<?php
declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Factory\Value;

use Akeneo\Pim\Enrichment\Component\Product\Factory\Value\ValueFactory;
use Akeneo\Pim\Enrichment\Component\Product\Value\NumberValue;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PhpSpec\ObjectBehavior;

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class NumberValueFactorySpec extends ObjectBehavior
{
    public function it_is_a_read_value_factory()
    {
        $this->shouldBeAnInstanceOf(ValueFactory::class);
    }

    public function it_supports_number_attribute_type()
    {
        $this->supportedAttributeType()->shouldReturn(AttributeTypes::NUMBER);
    }

    public function it_does_not_support_empty_values()
    {
        $this->shouldThrow(InvalidPropertyTypeException::class)->during('createByCheckingData', [
            $this->getAttribute(true, true),
            'ecommerce',
            'fr_FR',
            null
        ]);

        $this->shouldThrow(InvalidPropertyTypeException::class)->during('createByCheckingData', [
            $this->getAttribute(true, true),
            'ecommerce',
            'fr_FR',
            ' '
        ]);
    }

    public function it_does_not_support_non_numeric_types()
    {
        $this->shouldThrow(InvalidPropertyTypeException::class)->during('createByCheckingData', [
            $this->getAttribute(true, true),
            'ecommerce',
            'fr_FR',
            true
        ]);
    }

    public function it_generates_a_number_value_from_a_float()
    {
        $attribute = $this->getAttribute(false, false);
        $value = $this->createByCheckingData($attribute, null, null, 123.456);
        $value->shouldBeLike(NumberValue::value('an_attribute', 123.456));
    }

    public function it_generates_a_number_value_from_an_integer()
    {
        $attribute = $this->getAttribute(false, false);
        $value = $this->createByCheckingData($attribute, null, null, -11);
        $value->shouldBeLike(NumberValue::value('an_attribute', -11));
    }

    public function it_generates_a_number_value_from_a_non_empty_string()
    {
        $attribute = $this->getAttribute(false, false);
        $value = $this->createByCheckingData($attribute, null, null, '123.456');
        $value->shouldBeLike(NumberValue::value('an_attribute', '123.456'));
    }

    public function it_creates_a_localizable_and_scopable_value_without_checking_the_data()
    {
        $attribute = $this->getAttribute(true, true);
        $value = $this->createWithoutCheckingData($attribute, 'ecommerce', 'fr_FR', 1234567890);
        $value->shouldBeLike(NumberValue::scopableLocalizableValue('an_attribute', 1234567890, 'ecommerce', 'fr_FR'));
    }

    public function it_creates_a_localizable_value_without_checking_the_data()
    {
        $attribute = $this->getAttribute(true, false);
        $value = $this->createWithoutCheckingData($attribute, null, 'fr_FR', 1234567890);
        $value->shouldBeLike(NumberValue::localizableValue('an_attribute', 1234567890, 'fr_FR'));
    }

    public function it_creates_a_scopable_value_without_checking_the_data()
    {
        $attribute = $this->getAttribute(false, true);
        $value = $this->createWithoutCheckingData($attribute, 'ecommerce', null, 1234567890);
        $value->shouldBeLike(NumberValue::scopableValue('an_attribute', 1234567890, 'ecommerce'));
    }

    public function it_creates_a_non_localizable_and_non_scopable_value_without_checking_the_data()
    {
        $attribute = $this->getAttribute(false, false);
        $value = $this->createWithoutCheckingData($attribute, null, null, 1234567890);
        $value->shouldBeLike(NumberValue::value('an_attribute', 1234567890));
    }

    public function it_creates_a_non_localizable_and_non_scopable_value_without_checking_the_data_with_space()
    {
        $attribute = $this->getAttribute(false, false);
        $value = $this->createWithoutCheckingData($attribute, null, null, ' 1234567890');
        $value->shouldBeLike(NumberValue::value('an_attribute', 1234567890));
    }

    public function it_throws_an_exception_if_it_is_not_a_scalar()
    {
        $this->shouldThrow(InvalidPropertyTypeException::class)->during('createByCheckingData', [
            $this->getAttribute(true, true),
            'ecommerce',
            'fr_FR',
            new \stdClass()
        ]);
    }

    private function getAttribute(bool $isLocalizable, bool $isScopable): Attribute
    {
        return new Attribute('an_attribute', AttributeTypes::NUMBER, [], $isLocalizable, $isScopable, null, null, false, 'decimal', []);
    }
}
