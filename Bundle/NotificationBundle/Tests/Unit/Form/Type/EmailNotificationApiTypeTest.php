<?php

namespace Oro\Bundle\NotificationBundle\Tests\Unit\Form\Type;

use Oro\Bundle\NotificationBundle\Form\Type\EmailNotificationApiType;

class EmailNotificationApiTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EmailNotificationApiType
     */
    protected $type;

    protected function setUp()
    {
        $listener = $this->getMockBuilder('Oro\Bundle\EmailBundle\Form\EventListener\BuildNotificationFormListener')
            ->disableOriginalConstructor()
            ->getMock();

        $this->type = new EmailNotificationApiType(array(), $listener);
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
        $this->assertEquals('emailnotification_api', $this->type->getName());
    }

    public function testBuildForm()
    {
        $builder = $this->getMockBuilder('Symfony\Component\Form\FormBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        $builder->expects($this->at(0))
            ->method('addEventSubscriber')
            ->with($this->isInstanceOf('Oro\Bundle\EmailBundle\Form\EventListener\BuildNotificationFormListener'));

        $builder->expects($this->at(5))
            ->method('addEventSubscriber')
            ->with($this->isInstanceOf('Oro\Bundle\UserBundle\Form\EventListener\PatchSubscriber'));

        $this->type->buildForm($builder, array());
    }
}
