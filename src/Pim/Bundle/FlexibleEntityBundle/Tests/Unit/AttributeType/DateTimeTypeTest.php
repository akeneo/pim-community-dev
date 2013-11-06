<?php

namespace Pim\Bundle\FlexibleEntityBundle\Tests\Unit\AttributeType;

use Pim\Bundle\FlexibleEntityBundle\AttributeType\DateTimeType;

class DateTimeTypeTest extends AttributeTypeTest
{
    protected $name  = 'pim_flexibleentity_datetime';

    public function setUp()
    {
        parent::setUp();

        $this->target = new DateTimeType('integer', 'date', $this->guesser);
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
                'date',
                'bar',
                array_merge(
                    $this->defaultCreateNamedOptions,
                    array(
                        'widget' => 'single_text',
                        'input'  => 'datetime',
                    )
                )
            );

        $this->target->buildValueFormType($factory, $value);
    }

    public function testGetBackendType()
    {
        $this->assertEquals('integer', $this->target->getBackendType());
    }

    public function testGetFormType()
    {
        $this->assertEquals('date', $this->target->getFormType());
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
