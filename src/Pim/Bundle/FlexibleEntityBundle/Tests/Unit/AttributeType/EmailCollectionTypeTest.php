<?php
namespace Pim\Bundle\FlexibleEntityBundle\Tests\Unit\AttributeType;

use Pim\Bundle\FlexibleEntityBundle\AttributeType\EmailCollectionType;

class EmailCollectionTypeTest extends AttributeTypeTest
{
    protected $name  = 'pim_flexibleentity_email_collection';

    public function setUp()
    {
        parent::setUp();

        $this->target = new EmailCollectionType('collections', 'text', $this->guesser);
    }

    public function testBuildValueFormType()
    {
        $factory = $this->getFormFactoryMock();
        $email   = $this->getFlexibleValueMock(array());
        $value   = $this->getFlexibleValueMock(
            array(
                'data'        => $email,
                'backendType' => 'foo',
            )
        );

        $factory->expects($this->once())
            ->method('createNamed')
            ->with(
                'foo',
                'text',
                $email,
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
