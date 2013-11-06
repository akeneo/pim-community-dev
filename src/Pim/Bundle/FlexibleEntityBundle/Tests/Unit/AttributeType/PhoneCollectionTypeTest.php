<?php
namespace Pim\Bundle\FlexibleEntityBundle\Tests\Unit\AttributeType;

use Pim\Bundle\FlexibleEntityBundle\AttributeType\PhoneCollectionType;

class PhoneCollectionTypeTest extends AttributeTypeTest
{
    protected $name = 'pim_flexibleentity_phone_collection';

    public function setUp()
    {
        parent::setUp();

        $this->target = new PhoneCollectionType('collections', 'text', $this->guesser);
    }

    public function testBuildValueFormType()
    {
        $factory = $this->getFormFactoryMock();
        $phone = $this->getFlexibleValueMock(array());
        $value = $this->getFlexibleValueMock(
            array(
                'data'        => $phone,
                'backendType' => 'foo',
            )
        );

        $factory->expects($this->once())
            ->method('createNamed')
            ->with(
                'foo',
                'text',
                $phone,
                $this->defaultCreateNamedOptions
            );

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
