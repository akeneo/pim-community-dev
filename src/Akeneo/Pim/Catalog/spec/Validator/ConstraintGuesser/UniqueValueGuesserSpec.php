<?php

namespace spec\Pim\Component\Catalog\Validator\ConstraintGuesser;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Model\AttributeInterface;

class UniqueValueGuesserSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Catalog\Validator\ConstraintGuesser\UniqueValueGuesser');
    }

    public function it_is_an_attribute_constraint_guesser()
    {
        $this->shouldImplement('Pim\Component\Catalog\Validator\ConstraintGuesserInterface');
    }

    public function it_enforces_attribute_type(AttributeInterface $attribute)
    {
        foreach ($this->dataProviderForSupportedAttributes() as $attributeTypeTest) {
            $attributeType = $attributeTypeTest[0];
            $expectedResult = $attributeTypeTest[1];
            $attribute->getBackendType()
                ->willReturn($attributeType);
            $this->supportAttribute($attribute)->shouldReturn($expectedResult);
        }
    }

    public function it_guesses_unique_value(AttributeInterface $attribute)
    {
        $attribute->getBackendType()
            ->willReturn(AttributeTypes::BACKEND_TYPE_TEXT);
        $attribute->isUnique()->willReturn(true);
        $textConstraints = $this->guessConstraints($attribute);

        $textConstraints->shouldHaveCount(1);

        $firstConstraint = $textConstraints[0];
        $firstConstraint->shouldBeAnInstanceOf('Pim\Component\Catalog\Validator\Constraints\UniqueValue');
    }

    public function it_does_not_guess_unique_value(AttributeInterface $attribute)
    {
        $attribute->getBackendType()
            ->willReturn(AttributeTypes::BACKEND_TYPE_TEXT);

        $attribute->isUnique()->willReturn(false);

        $this->guessConstraints($attribute)
            ->shouldReturn([]);
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
