<?php

namespace Pim\Bundle\FlexibleEntityBundle\Tests\Unit\AttributeType;

use Pim\Bundle\FlexibleEntityBundle\AttributeType\BooleanType;

class BooleanTypeTest extends AttributeTypeTest
{
    protected $name = 'pim_flexibleentity_boolean';

    public function setUp()
    {
        parent::setUp();

        $this->target = new BooleanType('text', 'email', $this->guesser);
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
                'email',
                'bar',
                array(
                    'constraints'     => array('constraints'),
                    'label'           => null,
                    'required'        => null,
                    'auto_initialize' => null
                )
            );

        $this->target->buildValueFormType($factory, $value);
    }

    public function testGetBackendType()
    {
        $this->assertEquals('text', $this->target->getBackendType());
    }

    public function testGetFormType()
    {
        $this->assertEquals('email', $this->target->getFormType());
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
