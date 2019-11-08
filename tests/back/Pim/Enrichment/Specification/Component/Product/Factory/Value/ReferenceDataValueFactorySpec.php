<?php
declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Factory\Value;

use Akeneo\Pim\Enrichment\Component\Product\Factory\Value\ValueFactory;
use Akeneo\Pim\Enrichment\Component\Product\Value\ReferenceDataValue;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PhpSpec\ObjectBehavior;

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ReferenceDataValueFactorySpec extends ObjectBehavior
{
    public function it_is_a_read_value_factory()
    {
        $this->shouldBeAnInstanceOf(ValueFactory::class);
    }

    public function it_supports_reference_data_attribute_type()
    {
        $this->supportedAttributeType()->shouldReturn(AttributeTypes::REFERENCE_DATA_SIMPLE_SELECT);
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
        $value = $this->createWithoutCheckingData($attribute, 'ecommerce', 'fr_FR', 'blue');
        $value->shouldBeLike(ReferenceDataValue::scopableLocalizableValue('an_attribute', 'blue', 'ecommerce', 'fr_FR'));
    }

    public function it_creates_a_localizable_value()
    {
        $attribute = $this->getAttribute(true, false);
        $value = $this->createWithoutCheckingData($attribute, null, 'fr_FR', 'blue');
        $value->shouldBeLike(ReferenceDataValue::localizableValue('an_attribute', 'blue', 'fr_FR'));
    }

    public function it_creates_a_scopable_value()
    {
        $attribute = $this->getAttribute(false, true);
        $value = $this->createWithoutCheckingData($attribute, 'ecommerce', null, 'blue');
        $value->shouldBeLike(ReferenceDataValue::scopableValue('an_attribute', 'blue', 'ecommerce'));
    }

    public function it_creates_a_non_localizable_and_non_scopable_value()
    {
        $attribute = $this->getAttribute(false, false);
        $value = $this->createWithoutCheckingData($attribute, null, null, 'blue');
        $value->shouldBeLike(ReferenceDataValue::value('an_attribute', 'blue'));
    }


    private function getAttribute(bool $isLocalizable, bool $isScopable): Attribute
    {
        return new Attribute('an_attribute', AttributeTypes::REFERENCE_DATA_SIMPLE_SELECT, ['reference_data_name' => 'color'], $isLocalizable, $isScopable, null, false, 'reference_data_option', []);
    }
}
