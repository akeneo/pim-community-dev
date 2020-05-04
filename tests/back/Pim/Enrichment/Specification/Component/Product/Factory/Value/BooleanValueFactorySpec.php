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
final class BooleanValueFactorySpec extends ObjectBehavior
{
    public function it_is_a_read_value_factory()
    {
        $this->shouldBeAnInstanceOf(ValueFactory::class);
    }

    public function it_supports_boolean_attribute_types()
    {
        $this->supportedAttributeType()->shouldReturn(AttributeTypes::BOOLEAN);
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

    public function it_creates_a_localizable_and_scopable_value()
    {
        $attribute = $this->getAttribute(true, true);
        /** @var ScalarValue $value */
        $value = $this->createWithoutCheckingData($attribute, 'ecommerce', 'fr_FR', true);
        $value->shouldBeLike(ScalarValue::scopableLocalizableValue('an_attribute', true, 'ecommerce', 'fr_FR'));
    }

    public function it_creates_a_localizable_value()
    {
        $attribute = $this->getAttribute(true, false);
        /** @var ScalarValue $value */
        $value = $this->createWithoutCheckingData($attribute, null, 'fr_FR', true);
        $value->shouldBeLike(ScalarValue::localizableValue('an_attribute', true, 'fr_FR'));
    }

    public function it_creates_a_scopable_value()
    {
        $attribute = $this->getAttribute(false, true);
        /** @var ScalarValue $value */
        $value = $this->createWithoutCheckingData($attribute, 'ecommerce', null, true);
        $value->shouldBeLike(ScalarValue::scopableValue('an_attribute', true, 'ecommerce'));
    }

    public function it_creates_a_non_localizable_and_non_scopable_value()
    {
        $attribute = $this->getAttribute(false, false);
        /** @var ScalarValue $value */
        $value = $this->createWithoutCheckingData($attribute, null, null, true);
        $value->shouldBeLike(ScalarValue::value('an_attribute', true));
    }

    public function it_converts_to_boolean_type_the_value()
    {
        $attribute = $this->getAttribute(true, true);
        /** @var ScalarValue $value */
        $value = $this->createWithoutCheckingData($attribute, 'ecommerce', 'fr_FR', 1);
        $value->shouldBeLike(ScalarValue::scopableLocalizableValue('an_attribute', 1, 'ecommerce', 'fr_FR'));

        $value = $this->createWithoutCheckingData($attribute, 'ecommerce', 'fr_FR', '1');
        $value->shouldBeLike(ScalarValue::scopableLocalizableValue('an_attribute', 1, 'ecommerce', 'fr_FR'));
    }

    public function it_throws_an_exception_if_it_is_not_a_boolean()
    {
        $this->shouldThrow(InvalidPropertyTypeException::class)->during('createByCheckingData', [
            $this->getAttribute(true, true),
            'ecommerce',
            'fr_FR',
            'michel'
        ]);
    }

    private function getAttribute(bool $isLocalizable, bool $isScopable): Attribute
    {
        return new Attribute('an_attribute', AttributeTypes::BOOLEAN, [], $isLocalizable, $isScopable, null, null, false, 'boolean', []);
    }
}
