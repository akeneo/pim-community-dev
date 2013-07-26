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
        $listener = $this->getMockBuilder('Oro\Bundle\EmailBundle\Form\EventListener\BuildTemplateFormSubscriber')
            ->disableOriginalConstructor()
            ->getMock();

        $this->type = new EmailNotificationType(array(), $listener);
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

        $builder->expects($this->once())
            ->method('addEventSubscriber')
            ->with($this->isInstanceOf('Oro\Bundle\EmailBundle\Form\EventListener\BuildTemplateFormSubscriber'));

        $this->type->buildForm($builder, array());
    }
}
