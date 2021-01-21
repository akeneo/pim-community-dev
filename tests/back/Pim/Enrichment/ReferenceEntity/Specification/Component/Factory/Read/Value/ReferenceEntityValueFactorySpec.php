<?php
declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\ReferenceEntity\Component\Factory\Read\Value;

use Akeneo\Pim\Enrichment\Component\Product\Factory\Value\ValueFactory;
use Akeneo\Pim\Enrichment\ReferenceEntity\Component\AttributeType\ReferenceEntityType;
use Akeneo\Pim\Enrichment\ReferenceEntity\Component\Value\ReferenceEntityValue;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Akeneo\Tool\Component\StorageUtils\Exception\PropertyException;
use PhpSpec\ObjectBehavior;

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
final class ReferenceEntityValueFactorySpec extends ObjectBehavior
{
    public function it_is_a_read_value_factory()
    {
        $this->shouldBeAnInstanceOf(ValueFactory::class);
    }

    public function it_supports_reference_entity_attribute_type()
    {
        $this->supportedAttributeType()->shouldReturn(ReferenceEntityType::REFERENCE_ENTITY);
    }

    public function it_creates_a_localizable_and_scopable_value()
    {
        $attribute = $this->getAttribute(true, true);
        $value = $this->createWithoutCheckingData($attribute, 'ecommerce', 'fr_FR', 'michel');
        $value->shouldBeLike(ReferenceEntityValue::scopableLocalizableValue('an_attribute', RecordCode::fromString('michel'), 'ecommerce', 'fr_FR'));
    }

    public function it_creates_a_localizable_value()
    {
        $attribute = $this->getAttribute(true, false);
        $value = $this->createWithoutCheckingData($attribute, null, 'fr_FR', 'michel');
        $value->shouldBeLike(ReferenceEntityValue::localizableValue('an_attribute', RecordCode::fromString('michel'), 'fr_FR'));
    }

    public function it_creates_a_scopable_value()
    {
        $attribute = $this->getAttribute(false, true);
        $value = $this->createWithoutCheckingData($attribute, 'ecommerce', null, 'michel');
        $value->shouldBeLike(ReferenceEntityValue::scopableValue('an_attribute', RecordCode::fromString('michel'), 'ecommerce'));
    }

    public function it_creates_a_non_localizable_and_non_scopable_value()
    {
        $attribute = $this->getAttribute(false, false);
        $value = $this->createWithoutCheckingData($attribute, null, null, 'michel');
        $value->shouldBeLike(ReferenceEntityValue::value('an_attribute', RecordCode::fromString('michel')));
    }

    public function it_throws_an_exception_if_the_value_is_null()
    {
        $attribute = $this->getAttribute(true, true);
        $this->shouldThrow(InvalidPropertyTypeException::class)
            ->during('createByCheckingData', [
                $this->getAttribute(false, false),
                null,
                null,
                null
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

    public function it_throws_an_exception_when_record_code_is_invalid()
    {
        $this->shouldThrow(PropertyException::class)
            ->during('createByCheckingData', [
                $this->getAttribute(false, false),
                null,
                null,
                'an invalid record code'
            ]);
    }

    private function getAttribute(bool $isLocalizable, bool $isScopable): Attribute
    {
        return new Attribute('an_attribute', ReferenceEntityType::REFERENCE_ENTITY, ['reference_data_name' => 'color'], $isLocalizable, $isScopable, null, false, 'reference_data_option', []);
    }
}
