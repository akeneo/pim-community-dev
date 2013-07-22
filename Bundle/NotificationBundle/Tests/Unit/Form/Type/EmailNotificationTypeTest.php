<?php

namespace Oro\Bundle\NotificationBundle\Tests\Unit\Form\Type;

use Oro\Bundle\NotificationBundle\Form\Type\EmailNotificationType;

class EmailNotificationTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EmailNotificationType
     */
    protected $type;

    protected function setUp()
    {
        $this->type = new EmailNotificationType();
    }

    public function testSetDefaultOptions()
    {
        $resolver = $this->getMock('Symfony\Component\OptionsResolver\OptionsResolverInterface');
        $resolver->expects($this->once())
            ->method('setDefaults')
            ->with($this->isType('array'));

        $this->type->setDefaultOptions($resolver);
    }

    public function testGetName()
    {
        $this->assertEquals('emailnotification', $this->type->getName());
    }

    public function testBuildForm()
    {
        $builder = $this->getMockBuilder('Symfony\Component\Form\FormBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        $builder->expects($this->exactly(4))
            ->method('add');

        $this->type->buildForm($builder, array());
    }
}
