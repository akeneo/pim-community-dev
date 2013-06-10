<?php
namespace Oro\Bundle\FlexibleEntityBundle\Tests\Unit\AttributeType;

use Oro\Bundle\FlexibleEntityBundle\AttributeType\PhoneCollectionType;

class PhoneCollectionTypeTest extends AttributeTypeTest
{
    protected $name = 'oro_flexibleentity_phone_collection';

    public function setUp()
    {
        parent::setUp();

        $this->target = new PhoneCollectionType('collections', 'text', $this->guesser);
    }

    public function testBuildValueFormType()
    {
        $factory = $this->getFormFactoryMock();
        $phone = $this->getFlexibleValueMock(array());
        $value = $this->getFlexibleValueMock(array(
            'data'        => $phone,
            'backendType' => 'foo',
        ));

        $factory->expects($this->once())
            ->method('createNamed')
            ->with('foo', 'text',$phone, array(
                'constraints' => array('constraints'),
                'label'       => null,
                'required'    => null
            ));

        $this->target->buildValueFormType($factory, $value);
    }

    public function testGetBackendType()
    {
        $this->assertEquals('collections', $this->target->getBackendType());
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
