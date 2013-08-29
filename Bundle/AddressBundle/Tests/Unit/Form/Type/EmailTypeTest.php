<?php
namespace Oro\Bundle\AddressBundle\Tests\Unit\Form\Type;

use Oro\Bundle\AddressBundle\Form\Type\EmailType;

class EmailTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EmailType
     */
    protected $type;

    public function setUp()
    {
        $this->type = new EmailType();
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
            ->with('email', 'email');

        $builder->expects($this->at(2))
            ->method('add')
            ->with('primary', 'radio');

        $this->type->buildForm($builder, array());
    }

    public function testGetName()
    {
        $this->assertEquals('oro_email', $this->type->getName());
    }
}
