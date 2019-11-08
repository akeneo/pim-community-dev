<?php
declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Factory\Value;

use Akeneo\Pim\Enrichment\Component\Product\Factory\Value\ValueFactory;
use Akeneo\Pim\Enrichment\Component\Product\Value\OptionsValue;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PhpSpec\ObjectBehavior;

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class OptionsValueFactorySpec extends ObjectBehavior
{
    public function it_is_a_read_value_factory()
    {
        $this->shouldBeAnInstanceOf(ValueFactory::class);
    }

    public function it_supports_multi_select_attribute_type()
    {
        $this->supportedAttributeType()->shouldReturn(AttributeTypes::OPTION_MULTI_SELECT);
    }

    public function it_does_not_support_null()
    {
        $this->shouldThrow(\Throwable::class)->during('createByCheckingData', [
            $this->getAttribute(true, true),
            'ecommerce',
            'fr_FR',
            null
        ]);
    }

    public function it_creates_a_localizable_and_scopable_value()
    {
        $attribute = $this->getAttribute(true, true);
        $value = $this->createWithoutCheckingData($attribute, 'ecommerce', 'fr_FR', ['michel', 'sardou']);
        $value->shouldBeLike(OptionsValue::scopableLocalizableValue('an_attribute', ['michel', 'sardou'], 'ecommerce', 'fr_FR'));
    }

    public function it_creates_a_localizable_value()
    {
        $attribute = $this->getAttribute(true, false);
        $value = $this->createWithoutCheckingData($attribute, null, 'fr_FR', ['michel', 'sardou']);
        $value->shouldBeLike(OptionsValue::localizableValue('an_attribute', ['michel', 'sardou'], 'fr_FR'));
    }

    public function it_creates_a_scopable_value()
    {
        $attribute = $this->getAttribute(false, true);
        $value = $this->createWithoutCheckingData($attribute, 'ecommerce', null, ['michel', 'sardou']);
        $value->shouldBeLike(OptionsValue::scopableValue('an_attribute', ['michel', 'sardou'], 'ecommerce'));
    }

    public function it_creates_a_non_localizable_and_non_scopable_value()
    {
        $attribute = $this->getAttribute(false, false);
        $value = $this->createWithoutCheckingData($attribute, null, null, ['michel', 'sardou']);
        $value->shouldBeLike(OptionsValue::value('an_attribute', ['michel', 'sardou']));
    }

    public function it_sorts_the_result()
    {
        $attribute = $this->getAttribute(false, false);
        $value = $this->createWithoutCheckingData($attribute, null, null, ['sardou', 'michel']);
        $value->shouldBeLike(OptionsValue::value('an_attribute', ['michel', 'sardou']));
    }

    public function it_throws_an_exception_if_it_is_not_an_array()
    {
        $this->shouldThrow(InvalidPropertyTypeException::class)
            ->during('createByCheckingData', [
                $this->getAttribute(true, true),
                null,
                null,
                'foo'
            ]);
    }

    public function it_throws_an_exception_if_not_an_array_of_string()
    {
        $this->shouldThrow(InvalidPropertyTypeException::class)
            ->during('createByCheckingData', [
                $this->getAttribute(true, true),
                null,
                null,
                [new \stdClass()]
            ]);
    }

    private function getAttribute(bool $isLocalizable, bool $isScopable): Attribute
    {
        return new Attribute('an_attribute', AttributeTypes::OPTION_MULTI_SELECT, [], $isLocalizable, $isScopable, null, false, 'options', []);
    }
}
