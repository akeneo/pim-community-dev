<?php

namespace Oro\Bundle\FlexibleEntityBundle\Tests\Unit\AttributeType;

use Oro\Bundle\FlexibleEntityBundle\AttributeType\IntegerUnsignedType;

class IntegerUnsignedTypeTest extends AttributeTypeTest
{
    protected $name = 'oro_flexibleentity_integer_unsigned';

    public function setUp()
    {
        parent::setUp();

        $this->target = new IntegerUnsignedType('integer', 'number', $this->guesser);
    }

    public function testBuildValueFormType()
    {
        $factory = $this->getFormFactoryMock();
        $value = $this->getFlexibleValueMock(
            array(
                'data'        => 'bar',
                'backendType' => 'foo',
            )
        );

        $factory->expects($this->once())
            ->method('createNamed')
            ->with(
                'foo',
                'number',
                'bar',
                $this->defaultCreateNamedOptions
            );

        $this->target->buildValueFormType($factory, $value);
    }

    public function testGetBackendType()
    {
        $this->assertEquals('integer', $this->target->getBackendType());
    }

    public function testGetFormType()
    {
        $this->assertEquals('number', $this->target->getFormType());
    }

    public function testBuildAttributeFormTypes()
    {
        $this->assertEquals(
            array(),
            $this->target->buildAttributeFormTypes(
                $this->getFormFactoryMock(),
                $this->getAttributeMock(null, null)
            )
        );
    }
}
