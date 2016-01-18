<?php

namespace spec\Pim\Bundle\CatalogBundle\Validator\ConstraintGuesser;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\AttributeType\AbstractAttributeType;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;

class UniqueValueGuesserSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\CatalogBundle\Validator\ConstraintGuesser\UniqueValueGuesser');
    }

    public function it_is_an_attribute_constraint_guesser()
    {
        $this->shouldImplement('Pim\Bundle\CatalogBundle\Validator\ConstraintGuesserInterface');
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
            ->willReturn(AbstractAttributeType::BACKEND_TYPE_VARCHAR);
        $attribute->isUnique()->willReturn(true);
        $textConstraints = $this->guessConstraints($attribute);

        $textConstraints->shouldHaveCount(1);

        $firstConstraint = $textConstraints[0];
        $firstConstraint->shouldBeAnInstanceOf('Pim\Bundle\CatalogBundle\Validator\Constraints\UniqueValue');
    }

    public function it_does_not_guess_unique_value(AttributeInterface $attribute)
    {
        $attribute->getBackendType()
            ->willReturn(AbstractAttributeType::BACKEND_TYPE_VARCHAR);

        $attribute->isUnique()->willReturn(false);

        $this->guessConstraints($attribute)
            ->shouldReturn([]);
    }

    private function dataProviderForSupportedAttributes()
    {
        return [
            'boolean' => array(AbstractAttributeType::BACKEND_TYPE_BOOLEAN, false),
            'collection' => array(AbstractAttributeType::BACKEND_TYPE_COLLECTION, false),
            'date' => array(AbstractAttributeType::BACKEND_TYPE_DATE, true),
            'datetime' => array(AbstractAttributeType::BACKEND_TYPE_DATETIME, true),
            'decimal' => array(AbstractAttributeType::BACKEND_TYPE_DECIMAL, true),
            'entity' => array(AbstractAttributeType::BACKEND_TYPE_ENTITY, false),
            'integer' => array(AbstractAttributeType::BACKEND_TYPE_INTEGER, true),
            'media' => array(AbstractAttributeType::BACKEND_TYPE_MEDIA, false),
            'metric' => array(AbstractAttributeType::BACKEND_TYPE_METRIC, false),
            'option' => array(AbstractAttributeType::BACKEND_TYPE_OPTION, false),
            'options' => array(AbstractAttributeType::BACKEND_TYPE_OPTIONS, false),
            'price' => array(AbstractAttributeType::BACKEND_TYPE_PRICE, false),
            'text' => array(AbstractAttributeType::BACKEND_TYPE_TEXT, false),
            'varchar' => array(AbstractAttributeType::BACKEND_TYPE_VARCHAR, true),
        ];
    }
}
