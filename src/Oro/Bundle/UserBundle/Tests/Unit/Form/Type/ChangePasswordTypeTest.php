<?php

namespace Oro\Bundle\UserBundle\Tests\Unit\Type;

use Oro\Bundle\UserBundle\Form\Type\ChangePasswordType;
use Symfony\Component\Form\Test\FormIntegrationTestCase;

class ChangePasswordTypeTest extends FormIntegrationTestCase
{
    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    protected $subscriber;

    /** @var  ChangePasswordType */
    protected $type;

    public function setUp()
    {
        parent::setUp();

        $this->subscriber = $this->getMockBuilder('Oro\Bundle\UserBundle\Form\EventListener\ChangePasswordSubscriber')
            ->disableOriginalConstructor()
            ->getMock();

        $this->type = new ChangePasswordType($this->subscriber);
    }

    /**
     * Test buildForm
     */
    public function testBuildForm()
    {
        $builder = $this->createMock('Symfony\Component\Form\Test\FormBuilderInterface');
        $options = [];

        $builder->expects($this->once())
            ->method('addEventSubscriber')
            ->with($this->isInstanceOf('Oro\Bundle\UserBundle\Form\EventListener\ChangePasswordSubscriber'));

        $builder->expects($this->exactly(2))
            ->method('add')
            ->will($this->returnSelf());

        $this->type->buildForm($builder, $options);
    }

    /**
     * Test defaults
     */
    public function testSetDefaultOptions()
    {
        $resolver = $this->createMock('Symfony\Component\OptionsResolver\OptionsResolverInterface');
        $resolver->expects($this->once())
            ->method('setDefaults')
            ->with($this->isType('array'));
        $this->type->configureOptions($resolver);
    }

    /**
     * Test name
     */
    public function testName()
    {
        $this->assertEquals('oro_change_password', $this->type->getName());
    }
}
