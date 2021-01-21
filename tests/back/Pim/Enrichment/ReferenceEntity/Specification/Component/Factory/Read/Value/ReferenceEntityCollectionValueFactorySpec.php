<?php
declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\ReferenceEntity\Component\Factory\Read\Value;

use Akeneo\Pim\Enrichment\Component\Product\Factory\Value\ValueFactory;
use Akeneo\Pim\Enrichment\ReferenceEntity\Component\AttributeType\ReferenceEntityCollectionType;
use Akeneo\Pim\Enrichment\ReferenceEntity\Component\Value\ReferenceEntityCollectionValue;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\Tool\Component\StorageUtils\Exception\PropertyException;
use PhpSpec\ObjectBehavior;

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
final class ReferenceEntityCollectionValueFactorySpec extends ObjectBehavior
{
    public function it_is_a_read_value_factory()
    {
        $this->shouldBeAnInstanceOf(ValueFactory::class);
    }

    public function it_supports_reference_entity_attribute_type()
    {
        $this->supportedAttributeType()->shouldReturn(ReferenceEntityCollectionType::REFERENCE_ENTITY_COLLECTION);
    }

    public function it_creates_a_localizable_and_scopable_value()
    {
        $attribute = $this->getAttribute(true, true);
        $value = $this->createWithoutCheckingData($attribute, 'ecommerce', 'fr_FR', ['blue', 'green']);
        $value->shouldBeLike(ReferenceEntityCollectionValue::scopableLocalizableValue('an_attribute', [RecordCode::fromString('blue'), RecordCode::fromString('green')], 'ecommerce', 'fr_FR'));
    }

    public function it_creates_a_localizable_value()
    {
        $attribute = $this->getAttribute(true, false);
        $value = $this->createWithoutCheckingData($attribute, null, 'fr_FR', ['blue', 'green']);
        $value->shouldBeLike(ReferenceEntityCollectionValue::localizableValue('an_attribute', [RecordCode::fromString('blue'), RecordCode::fromString('green')], 'fr_FR'));
    }

    public function it_creates_a_scopable_value()
    {
        $attribute = $this->getAttribute(false, true);
        $value = $this->createWithoutCheckingData($attribute, 'ecommerce', null, ['blue', 'green']);
        $value->shouldBeLike(ReferenceEntityCollectionValue::scopableValue('an_attribute', [RecordCode::fromString('blue'), RecordCode::fromString('green')], 'ecommerce'));
    }

    public function it_creates_a_non_localizable_and_non_scopable_value()
    {
        $attribute = $this->getAttribute(false, false);
        $value = $this->createWithoutCheckingData($attribute, null, null, ['blue', 'green']);
        $value->shouldBeLike(ReferenceEntityCollectionValue::value('an_attribute', [RecordCode::fromString('blue'), RecordCode::fromString('green')]));
    }

    public function it_throws_an_exception_when_a_record_code_is_invalid()
    {
        $this->shouldThrow(PropertyException::class)
            ->during('createByCheckingData', [
                $this->getAttribute(true, true),
                null,
                null,
                ['green', 'an invalid record code']
            ]);
    }

    private function getAttribute(bool $isLocalizable, bool $isScopable): Attribute
    {
        return new Attribute('an_attribute', ReferenceEntityCollectionType::REFERENCE_ENTITY_COLLECTION, ['reference_data_name' => 'color'], $isLocalizable, $isScopable, null, false, 'reference_data_options', []);
    }
}
