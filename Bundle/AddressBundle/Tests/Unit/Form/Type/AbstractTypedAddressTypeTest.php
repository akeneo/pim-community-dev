<?php

namespace Oro\Bundle\AddressBundle\Tests\Unit\Form\Type;

use Oro\Bundle\AddressBundle\Form\Type\AbstractTypedAddressType;

class AbstractTypedAddressTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|AbstractTypedAddressType
     */
    protected $type;

    /**
     * Setup test env
     */
    public function setUp()
    {
        $this->type = $this->getMockForAbstractClass('Oro\Bundle\AddressBundle\Form\Type\AbstractTypedAddressType');
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
}
