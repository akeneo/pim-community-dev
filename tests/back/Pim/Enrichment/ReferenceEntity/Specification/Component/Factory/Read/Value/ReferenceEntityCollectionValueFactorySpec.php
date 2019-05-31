<?php
declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\ReferenceEntity\Component\Factory\Read\Value;

use Akeneo\Pim\Enrichment\Component\Product\Factory\Read\Value\ReadValueFactory;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Pim\Enrichment\ReferenceEntity\Component\AttributeType\ReferenceEntityCollectionType;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use PhpSpec\ObjectBehavior;

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
final class ReferenceEntityCollectionValueFactorySpec extends ObjectBehavior
{
    public function it_is_a_read_value_factory()
    {
        $this->shouldBeAnInstanceOf(ReadValueFactory::class);
    }

    public function it_supports_reference_entity_attribute_type()
    {
        $this->supportedAttributeType()->shouldReturn(ReferenceEntityCollectionType::REFERENCE_ENTITY_COLLECTION);
    }

    public function it_creates_a_localizable_and_scopable_value()
    {
        $attribute = $this->getAttribute(true, true);
        /** @var ScalarValue $value */
        $value = $this->create($attribute, 'ecommerce', 'fr_FR', ['blue', 'green']);
        $value->isLocalizable()->shouldBe(true);
        $value->isScopable()->shouldBe(true);
        $value->getData()->shouldBeLike([RecordCode::fromString('blue'), RecordCode::fromString('green')]);
    }

    public function it_creates_a_localizable_value()
    {
        $attribute = $this->getAttribute(true, false);
        /** @var ScalarValue $value */
        $value = $this->create($attribute, null, 'fr_FR', ['blue', 'green']);
        $value->isLocalizable()->shouldBe(true);
        $value->isScopable()->shouldBe(false);
        $value->getData()->shouldBeLike([RecordCode::fromString('blue'), RecordCode::fromString('green')]);
    }

    public function it_creates_a_scopable_value()
    {
        $attribute = $this->getAttribute(false, true);
        /** @var ScalarValue $value */
        $value = $this->create($attribute, 'ecommerce', null, ['blue', 'green']);
        $value->isLocalizable()->shouldBe(false);
        $value->isScopable()->shouldBe(true);
        $value->getData()->shouldBeLike([RecordCode::fromString('blue'), RecordCode::fromString('green')]);
    }

    public function it_creates_a_non_localizable_and_non_scopable_value()
    {
        $attribute = $this->getAttribute(false, false);
        /** @var ScalarValue $value */
        $value = $this->create($attribute, null, null, ['blue', 'green']);
        $value->isLocalizable()->shouldBe(false);
        $value->isScopable()->shouldBe(false);
        $value->getData()->shouldBeLike([RecordCode::fromString('blue'), RecordCode::fromString('green')]);
    }

    private function getAttribute(bool $isLocalizable, bool $isScopable): Attribute
    {
        return new Attribute('an_attribute', ReferenceEntityCollectionType::REFERENCE_ENTITY_COLLECTION, ['reference_data_name' => 'color'], $isLocalizable, $isScopable, null, false);
    }
}
