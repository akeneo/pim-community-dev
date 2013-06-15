<?php

namespace Pim\Bundle\ProductBundle\Tests\Unit\EventListener;

use Pim\Bundle\ProductBundle\EventListener\LocaleListener;
use Symfony\Component\HttpKernel\HttpKernel;

/**
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LocaleListenerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->translatableListener = $this->getTranslatableListenerMock();
        $this->securityContext = $this->getSecurityContextMock();
        $this->target = new LocaleListener($this->securityContext, $this->translatableListener);
    }

    public function testEventSubscriberInstance()
    {
        $this->assertInstanceOf('Symfony\Component\EventDispatcher\EventSubscriberInterface', $this->target);
    }

    public function testGetSubscribedEvent()
    {
        $this->assertEquals(array(
            'kernel.request' => 'onKernelRequest',
        ), $this->target->getSubscribedEvents());
    }

    public function testOnKernelRequest()
    {
        $user = $this->getUserMock('fr_FR');
        $token = $this->getTokenMock($user);
        $this->securityContext
             ->expects($this->any())
             ->method('getToken')
             ->will($this->returnValue($token));

        $this->translatableListener
             ->expects($this->once())
             ->method('setTranslatableLocale')
             ->with($this->equalTo('fr_FR'));

        $this->target->onKernelRequest($this->getGetResponseEventMock());
    }

    public function testOnKernelRequestWithNoToken()
    {
        $this->securityContext
             ->expects($this->any())
             ->method('getToken')
             ->will($this->returnValue(null));

        $this->translatableListener
             ->expects($this->never())
             ->method('setTranslatableLocale');

        $this->target->onKernelRequest($this->getGetResponseEventMock());
    }

    public function testOnKernelRequestWithNoUser()
    {
        $token = $this->getTokenMock(null);
        $this->securityContext
             ->expects($this->any())
             ->method('getToken')
             ->will($this->returnValue($token));

        $this->translatableListener
             ->expects($this->never())
             ->method('setTranslatableLocale');

        $this->target->onKernelRequest($this->getGetResponseEventMock());
    }

    public function testOnKernelRequestWithSubRequest()
    {
        $user = $this->getUserMock('fr_FR');
        $token = $this->getTokenMock($user);
        $this->securityContext
             ->expects($this->any())
             ->method('getToken')
             ->will($this->returnValue($token));

        $this->translatableListener
             ->expects($this->never())
             ->method('setTranslatableLocale');

        $this->target->onKernelRequest($this->getGetResponseEventMock(HttpKernel::SUB_REQUEST));
    }

    private function getTranslatableListenerMock()
    {
        return $this->getMock('Gedmo\Translatable\TranslatableListener');
    }

    private function getSecurityContextMock()
    {
        return $this->getMock('Symfony\Component\Security\Core\SecurityContextInterface', array(
            'getToken', 'setToken', 'isGranted'
        ));
    }

    private function getGetResponseEventMock($type = HttpKernel::MASTER_REQUEST)
    {
       $event = $this
            ->getMockBuilder('Symfony\Component\HttpKernel\Event\GetResponseEvent')
            ->setMethods(array('getRequestType'))
            ->disableOriginalConstructor()
            ->getMock()
        ;

       $event->expects($this->any())
             ->method('getRequestType')
             ->will($this->returnValue($type));

       return $event;
    }

    private function getTokenMock($user)
    {
        $token = $this->getMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface', array(
            'getUser', 'setUser', '__toString', 'getRoles', 'getCredentials', 'setUsername', 'getUsername', 'isAuthenticated',
            'setAuthenticated', 'eraseCredentials', 'getAttributes', 'setAttributes', 'hasAttribute', 'getAttribute',
            'setAttribute', 'serialize', 'unserialize'
        ));

        $token->expects($this->any())
              ->method('getUser')
              ->will($this->returnValue($user));

        return $token;
    }

    private function getUserMock($catalogLocale)
    {
        $user = $this->getMock('Symfony\Component\Security\Core\User\UserInterface', array(
            'getValue', 'getRoles', 'getPassword', 'getSalt', 'getUsername', 'eraseCredentials'
        ));

        $value = $this->getMock('Oro\Bundle\UserBundle\Entity\UserValue', array('getData'));

        $user->expects($this->any())
             ->method('getValue')
             ->with($this->equalTo('cataloglocale'))
             ->will($this->returnValue($value));

        $value->expects($this->any())
              ->method('getData')
              ->will($this->returnValue($catalogLocale));

        return $user;
    }
}
