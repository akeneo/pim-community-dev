<?php

namespace Oro\Bundle\AddressBundle\Tests\Unit\Form\Type;

use Oro\Bundle\AddressBundle\Form\Type\TypedAddressType;

class TypedAddressTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|TypedAddressType
     */
    protected $type;

    /**
     * Setup test env
     */
    public function setUp()
    {
        $this->type = new TypedAddressType();
    }

    public function testBuildForm()
    {
        $builder = $this->getMockBuilder('Symfony\Component\Form\FormBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        $builder->expects($this->at(0))
            ->method('add')
            ->with(
                'types',
                'translatable_entity',
                array(
                    'class'    => 'OroAddressBundle:AddressType',
                    'property' => 'label',
                    'required' => false,
                    'multiple' => true,
                    'expanded' => true,
                )
            )
            ->will($this->returnSelf());

        $builder->expects($this->at(1))
            ->method('add')
            ->with(
                'primary',
                'checkbox',
                array(
                    'label' => 'Primary',
                    'required' => false
                )
            )
            ->will($this->returnSelf());

        $this->type->buildForm($builder, array());
    }

    public function testGetParent()
    {
        $this->assertEquals('oro_address', $this->type->getParent());
    }

    public function testGetName()
    {
        $this->assertEquals('oro_typed_address', $this->type->getName());
    }
}
