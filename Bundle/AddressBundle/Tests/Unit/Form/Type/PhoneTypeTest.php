<?php
namespace Oro\Bundle\AddressBundle\Tests\Unit\Form\Type;

use Oro\Bundle\AddressBundle\Form\Type\PhoneType;

class PhoneTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PhoneType
     */
    protected $type;

    /**
     * Setup test env
     */
    public function setUp()
    {
        $this->type = new PhoneType();
    }

    public function testBuildForm()
    {
        $builder = $this->getMockBuilder('Symfony\Component\Form\FormBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        $builder->expects($this->exactly(3))
            ->method('add')
            ->will($this->returnSelf());

        $builder->expects($this->at(0))
            ->method('add')
            ->with('id', 'hidden');

        $builder->expects($this->at(1))
            ->method('add')
            ->with('phone', 'text');

        $builder->expects($this->at(2))
            ->method('add')
            ->with('primary', 'checkbox');

        $this->type->buildForm($builder, array());
    }

    public function testGetName()
    {
        $this->assertEquals('oro_phone', $this->type->getName());
    }
}
