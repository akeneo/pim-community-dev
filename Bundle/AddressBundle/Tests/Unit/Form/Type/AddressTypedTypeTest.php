<?php

namespace Oro\Bundle\AddressBundle\Tests\Unit\Type;

use Oro\Bundle\AddressBundle\Form\Type\AddressTypedType;

class AddressTypedTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AddressTypedType
     */
    protected $type;

    /**
     * Setup test env
     */
    public function setUp()
    {
        $buildAddressFormListener = $this->getMockBuilder('Oro\Bundle\AddressBundle\Form\EventListener\BuildAddressFormListener')
            ->disableOriginalConstructor()
            ->getMock();
        $flexibleManager = $this->getMockBuilder('Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->type = new AddressTypedType(
            $flexibleManager,
            'oro_address_value',
            $buildAddressFormListener
        );
    }

    public function testAddEntityFields()
    {
        $builder = $this->getMockBuilder('Symfony\Component\Form\FormBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $builder->expects($this->any())
            ->method('add')
            ->will($this->returnSelf());
        $builder->expects($this->at(0))
            ->method('add')
            ->with(
                'types',
                'entity',
                $this->isType('array')
            );
        $this->type->addEntityFields($builder);
    }

    public function testGetName()
    {
        $this->assertEquals('oro_address_typed', $this->type->getName());
    }
}
