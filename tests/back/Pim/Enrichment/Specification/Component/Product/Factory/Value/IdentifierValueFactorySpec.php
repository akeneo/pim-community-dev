<?php
declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Factory\Value;

use Akeneo\Pim\Enrichment\Component\Product\Factory\Value\ValueFactory;
use Akeneo\Pim\Enrichment\Component\Product\Value\IdentifierValue;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PhpSpec\ObjectBehavior;

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class IdentifierValueFactorySpec extends ObjectBehavior
{
    public function it_is_a_read_value_factory(): void
    {
        $this->shouldBeAnInstanceOf(ValueFactory::class);
    }

    public function it_supports_identifier_attribute_type(): void
    {
        $this->supportedAttributeType()->shouldReturn(AttributeTypes::IDENTIFIER);
    }

    public function it_does_not_support_null(): void
    {
        $this->shouldThrow(InvalidPropertyTypeException::class)->during('createByCheckingData', [
            $this->getAttribute(true, true),
            'ecommerce',
            'fr_FR',
            null
        ]);
    }

    public function it_cannot_create_a_localizable_and_scopable_value(): void
    {
        $attribute = $this->getAttribute(true, true);

        $this->shouldThrow(\InvalidArgumentException::class)->during(
            'createByCheckingData',
            [$attribute, 'ecommerce', 'fr_FR', 'my_identifier']
        );
    }

    public function it_cannot_create_a_localizable_value(): void
    {
        $attribute = $this->getAttribute(true, false);

        $this->shouldThrow(\InvalidArgumentException::class)->during(
            'createByCheckingData',
            [$attribute, null, 'fr_FR', 'my_identifier']
        );
    }

    public function it_cannot_create_a_scopable_value(): void
    {
        $attribute = $this->getAttribute(false, true);

        $this->shouldThrow(\InvalidArgumentException::class)->during(
            'createByCheckingData',
            [$attribute, 'ecommerce', null, 'my_identifier']
        );
    }

    public function it_cannot_create_a_value_with_a_non_string_value(): void
    {
        $attribute = $this->getAttribute(false, false);

        $this->shouldThrow(InvalidPropertyTypeException::class)->during(
            'createByCheckingData',
            [$attribute, null, null, 42]
        );
    }

    public function it_cannot_create_a_value_with_an_empty_string_value(): void
    {
        $attribute = $this->getAttribute(false, false);

        $this->shouldThrow(InvalidPropertyException::class)->during(
            'createByCheckingData',
            [$attribute, null, null, '']
        );
    }

    public function it_throws_an_exception_if_it_is_not_a_string(): void
    {
        $this->shouldThrow(InvalidPropertyTypeException::class)->during('createByCheckingData', [
            $this->getAttribute(true, true),
            'ecommerce',
            'fr_FR',
            new \stdClass()
        ]);
    }

    public function it_creates_a_value_for_the_main_identifier_attribute(): void
    {
        $attribute = $this->getAttribute(false, false);
        $value = $this->createByCheckingData($attribute, null, null, 'my_identifier');

        $value->shouldBeLike(IdentifierValue::value('an_attribute', true, 'my_identifier'));
    }

    public function it_creates_a_value_for_another_identifier_attribute(): void
    {
        $attribute = $this->getAttribute(false, false, false);
        $value = $this->createByCheckingData($attribute, null, null, 'my_identifier');

        $value->shouldBeLike(IdentifierValue::value('an_attribute', false, 'my_identifier'));
    }

    private function getAttribute(bool $isLocalizable, bool $isScopable, bool $isMainIdentifier = true): Attribute
    {
        return new Attribute(
            'an_attribute',
            AttributeTypes::IDENTIFIER,
            [],
            $isLocalizable,
            $isScopable,
            null,
            null,
            false,
            'text',
            [],
            null,
            [],
            $isMainIdentifier
        );
    }
}
