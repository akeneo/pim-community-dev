<?php

namespace Oro\Bundle\UserBundle\Tests\Unit\Type;

use Oro\Bundle\UserBundle\Form\EventListener\ChangePasswordSubscriber;

use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\Test\FormIntegrationTestCase;

class ChangePasswordSubscriberTest extends FormIntegrationTestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $aclManager;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $securityContext;

    /** @var  ChangePasswordSubscriber */
    protected $subscriber;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $token;

    public function setUp()
    {
        parent::setUp();

        $this->aclManager = $this->getMockBuilder('Oro\Bundle\UserBundle\Acl\Manager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->securityContext = $this->getMockForAbstractClass(
            'Symfony\Component\Security\Core\SecurityContextInterface'
        );

        $this->token = $this
            ->getMockBuilder('Symfony\Component\Security\Core\Authentication\Token\TokenInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->subscriber = new ChangePasswordSubscriber($this->factory, $this->aclManager, $this->securityContext);
    }

    /**
     * test getSubscribedEvents
     */
    public function testSubscribedEvents()
    {
        $this->assertEquals(
            array(
                FormEvents::POST_SUBMIT => 'onSubmit',
                FormEvents::PRE_SUBMIT   => 'preSubmit'
            ),
            $this->subscriber->getSubscribedEvents()
        );
    }

    /**
     * Test onSubmit
     */
    public function testOnSubmit()
    {
        $eventMock = $this->getMockBuilder('Symfony\Component\Form\FormEvent')
            ->disableOriginalConstructor()
            ->getMock();

        $formMock = $this->getMockBuilder('Symfony\Component\Form\Test\FormInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $parentFormMock = $this->getMockBuilder('Symfony\Component\Form\Test\FormInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $formMock->expects($this->once())
            ->method('getParent')
            ->will($this->returnValue($parentFormMock));

        $formPlainPassword = $this->getMockBuilder('Symfony\Component\Form\Test\FormInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $formPlainPassword->expects($this->once())
            ->method('getData')
            ->will($this->returnValue('123123'));

        $formMock->expects($this->once())
            ->method('get')
            ->with($this->equalTo('plainPassword'))
            ->will($this->returnValue($formPlainPassword));

        $currentUser = $userMock = $this
            ->getMockBuilder('Oro\Bundle\UserBundle\Entity\User')
            ->disableOriginalConstructor()
            ->getMock();

        $userMock->expects($this->exactly(3))
            ->method('getId')
            ->will($this->returnValue(1));

        $this->token->expects($this->any())
            ->method('getUser')
            ->will($this->returnValue($currentUser));

        $this->securityContext->expects($this->once())
            ->method('getToken')
            ->will($this->returnValue($this->token));

        $parentFormMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($userMock));

        $eventMock->expects($this->once())
            ->method('getForm')
            ->will($this->returnValue($formMock));

        $this->subscriber->onSubmit($eventMock);
    }

    /**
     * Test bad scenario for isCurrentUser
     */
    public function testIsCurrentUserFalse()
    {
        $reflection = new \ReflectionMethod($this->subscriber, 'isCurrentUser');
        $reflection->setAccessible(true);

        $userMock = $this
            ->getMockBuilder('Oro\Bundle\UserBundle\Entity\User')
            ->disableOriginalConstructor()
            ->getMock();

        $userMock->expects($this->once())
            ->method('getId')
            ->will($this->returnValue(1));

        $this->token->expects($this->any())
            ->method('getUser')
            ->will($this->returnValue(null));

        $this->securityContext->expects($this->once())
            ->method('getToken')
            ->will($this->returnValue($this->token));

        return $reflection->invoke($this->subscriber, $userMock);
    }
}
