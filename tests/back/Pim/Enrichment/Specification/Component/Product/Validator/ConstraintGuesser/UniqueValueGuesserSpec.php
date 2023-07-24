<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Validator\ConstraintGuesser;

use Akeneo\Pim\Enrichment\Component\Product\Validator\ConstraintGuesser\UniqueValueGuesser;
use Akeneo\Pim\Enrichment\Component\Product\Validator\ConstraintGuesserInterface;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\UniqueValue;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;

class UniqueValueGuesserSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(UniqueValueGuesser::class);
    }

    public function it_is_an_attribute_constraint_guesser()
    {
        $this->shouldImplement(ConstraintGuesserInterface::class);
    }

    public function it_enforces_attribute_type(AttributeInterface $attribute)
    {
        foreach ($this->dataProviderForSupportedAttributes() as $attributeType => $attributeTypeTest) {
            $attributeBackendType = $attributeTypeTest[0];
            $expectedResult = $attributeTypeTest[1];
            $attribute->getBackendType()->willReturn($attributeBackendType);
            $attribute->getType()->willReturn('pim_catalog_' . $attributeType);
            $attribute->isMainIdentifier()->willReturn(false);
            $this->supportAttribute($attribute)->shouldReturn($expectedResult);
        }
    }

    public function it_does_not_guess_constraints_for_the_main_identifier_attribute(AttributeInterface $attribute)
    {
        $attribute->getBackendType()->willReturn(AttributeTypes::BACKEND_TYPE_TEXT);
        $attribute->isUnique()->willReturn(true);
        $attribute->isMainIdentifier()->willReturn(true);

        $this->supportAttribute($attribute)->shouldReturn(false);
        $this->guessConstraints($attribute)->shouldReturn([]);
    }

    public function it_guesses_constraints_for_unique_value(AttributeInterface $attribute)
    {
        $attribute->getBackendType()->willReturn(AttributeTypes::BACKEND_TYPE_TEXT);
        $attribute->isUnique()->willReturn(true);
        $attribute->getType()->willReturn(AttributeTypes::TEXT);
        $attribute->isMainIdentifier()->willReturn(false);

        $this->guessConstraints($attribute)->shouldBeLike([new UniqueValue()]);
    }

    public function it_does_not_guess_constraints_for_non_unique_values(AttributeInterface $attribute)
    {
        $attribute->getBackendType()->willReturn(AttributeTypes::BACKEND_TYPE_TEXT);
        $attribute->getType()->willReturn(AttributeTypes::TEXT);
        $attribute->isUnique()->willReturn(false);

        $this->guessConstraints($attribute)->shouldReturn([]);
    }

    public function it_changes_the_erro_message_for_identifier_attributes(AttributeInterface $attribute)
    {
        $attribute->isUnique()->willReturn(true);
        $attribute->getType()->willReturn(AttributeTypes::IDENTIFIER);
        $attribute->isMainIdentifier()->willReturn(false);

        $this->guessConstraints($attribute)->shouldBeLike(
            [new UniqueValue(['message' => 'pim_catalog.constraint.unique_identifier_value'])]
        );
    }

    private function dataProviderForSupportedAttributes()
    {
        return [
            'boolean'    => [AttributeTypes::BACKEND_TYPE_BOOLEAN, false],
            'collection' => [AttributeTypes::BACKEND_TYPE_COLLECTION, false],
            'date'       => [AttributeTypes::BACKEND_TYPE_DATE, true],
            'datetime'   => [AttributeTypes::BACKEND_TYPE_DATETIME, true],
            'decimal'    => [AttributeTypes::BACKEND_TYPE_DECIMAL, true],
            'entity'     => [AttributeTypes::BACKEND_TYPE_ENTITY, false],
            'media'      => [AttributeTypes::BACKEND_TYPE_MEDIA, false],
            'metric'     => [AttributeTypes::BACKEND_TYPE_METRIC, false],
            'option'     => [AttributeTypes::BACKEND_TYPE_OPTION, false],
            'options'    => [AttributeTypes::BACKEND_TYPE_OPTIONS, false],
            'price'      => [AttributeTypes::BACKEND_TYPE_PRICE, false],
            'textarea'   => [AttributeTypes::BACKEND_TYPE_TEXTAREA, false],
            'text'       => [AttributeTypes::BACKEND_TYPE_TEXT, true],
        ];
    }
}
