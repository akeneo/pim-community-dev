<?php

namespace Oro\Bundle\FlexibleEntityBundle\Tests\Unit\AttributeType;

use Oro\Bundle\FlexibleEntityBundle\AttributeType\ImageType;

class ImageTypeTest extends AttributeTypeTest
{
    protected $name = 'oro_flexibleentity_image';

    public function setUp()
    {
        parent::setUp();

        $this->target = new ImageType('varchar', 'text', $this->guesser);
    }

    public function testBuildValueFormType()
    {
        $factory = $this->getFormFactoryMock();
        $value = $this->getFlexibleValueMock(array(
            'data'        => 'bar',
            'backendType' => 'foo',
        ));

        $factory->expects($this->once())
            ->method('createNamed')
            ->with('foo', 'text', 'bar', array(
                'constraints' => array('constraints'),
                'label'       => null,
                'required'    => null,
            ));

        $this->target->buildValueFormType($factory, $value);
    }

    public function testGetBackendType()
    {
        $this->assertEquals('varchar', $this->target->getBackendType());
    }

    public function testGetFormType()
    {
        $this->assertEquals('text', $this->target->getFormType());
    }

    public function testBuildAttributeFormType()
    {
        $this->assertNull($this->target->buildAttributeFormType(
            $this->getFormFactoryMock(),
            $this->getAttributeMock(null, null)
        ));
    }
}
