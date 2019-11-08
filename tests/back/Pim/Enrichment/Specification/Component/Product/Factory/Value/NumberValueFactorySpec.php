<?php
declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Factory\Value;

use Akeneo\Pim\Enrichment\Component\Product\Factory\Value\ValueFactory;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
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

    public function it_does_not_support_null()
    {
        $this->shouldThrow(InvalidPropertyTypeException::class)->during('createByCheckingData', [
            $this->getAttribute(true, true),
            'ecommerce',
            'fr_FR',
            null
        ]);
    }

    public function it_creates_a_localizable_and_scopable_value_without_checking_the_data()
    {
        $attribute = $this->getAttribute(true, true);
        /** @var ScalarValue $value */
        $value = $this->createWithoutCheckingData($attribute, 'ecommerce', 'fr_FR', 1234567890);
        $value->shouldBeLike(ScalarValue::scopableLocalizableValue('an_attribute', 1234567890, 'ecommerce', 'fr_FR'));
    }

    public function it_creates_a_localizable_value_without_checking_the_data()
    {
        $attribute = $this->getAttribute(true, false);
        /** @var ScalarValue $value */
        $value = $this->createWithoutCheckingData($attribute, null, 'fr_FR', 1234567890);
        $value->shouldBeLike(ScalarValue::localizableValue('an_attribute', 1234567890, 'fr_FR'));
    }

    public function it_creates_a_scopable_value_without_checking_the_data()
    {
        $attribute = $this->getAttribute(false, true);
        /** @var ScalarValue $value */
        $value = $this->createWithoutCheckingData($attribute, 'ecommerce', null, 1234567890);
        $value->shouldBeLike(ScalarValue::scopableValue('an_attribute', 1234567890, 'ecommerce'));
    }

    public function it_creates_a_non_localizable_and_non_scopable_value_without_checking_the_data()
    {
        $attribute = $this->getAttribute(false, false);
        /** @var ScalarValue $value */
        $value = $this->createWithoutCheckingData($attribute, null, null, 1234567890);
        $value->shouldBeLike(ScalarValue::value('an_attribute', 1234567890));
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
        return new Attribute('an_attribute', AttributeTypes::NUMBER, [], $isLocalizable, $isScopable, null, false, 'decimal', []);
    }
}
