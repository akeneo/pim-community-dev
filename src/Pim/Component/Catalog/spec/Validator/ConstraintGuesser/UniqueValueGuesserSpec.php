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
            ->willReturn(AttributeTypes::BACKEND_TYPE_VARCHAR);
        $attribute->isUnique()->willReturn(true);
        $textConstraints = $this->guessConstraints($attribute);

        $textConstraints->shouldHaveCount(1);

        $firstConstraint = $textConstraints[0];
        $firstConstraint->shouldBeAnInstanceOf('Pim\Component\Catalog\Validator\Constraints\UniqueValue');
    }

    public function it_does_not_guess_unique_value(AttributeInterface $attribute)
    {
        $attribute->getBackendType()
            ->willReturn(AttributeTypes::BACKEND_TYPE_VARCHAR);

        $attribute->isUnique()->willReturn(false);

        $this->guessConstraints($attribute)
            ->shouldReturn([]);
    }

    private function dataProviderForSupportedAttributes()
    {
        return [
            'boolean' => array(AttributeTypes::BACKEND_TYPE_BOOLEAN, false),
            'collection' => array(AttributeTypes::BACKEND_TYPE_COLLECTION, false),
            'date' => array(AttributeTypes::BACKEND_TYPE_DATE, true),
            'datetime' => array(AttributeTypes::BACKEND_TYPE_DATETIME, true),
            'decimal' => array(AttributeTypes::BACKEND_TYPE_DECIMAL, true),
            'entity' => array(AttributeTypes::BACKEND_TYPE_ENTITY, false),
            'integer' => array(AttributeTypes::BACKEND_TYPE_INTEGER, true),
            'media' => array(AttributeTypes::BACKEND_TYPE_MEDIA, false),
            'metric' => array(AttributeTypes::BACKEND_TYPE_METRIC, false),
            'option' => array(AttributeTypes::BACKEND_TYPE_OPTION, false),
            'options' => array(AttributeTypes::BACKEND_TYPE_OPTIONS, false),
            'price' => array(AttributeTypes::BACKEND_TYPE_PRICE, false),
            'text' => array(AttributeTypes::BACKEND_TYPE_TEXT, false),
            'varchar' => array(AttributeTypes::BACKEND_TYPE_VARCHAR, true),
        ];
    }
}
