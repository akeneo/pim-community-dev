<?php

namespace Pim\Bundle\CatalogBundle\Tests\Unit\EventListener;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernel;
use Pim\Bundle\CatalogBundle\EventListener\UserContextListener;

/**
 * Test related class
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UserContextListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Symfony\Component\Security\Core\SecurityContextInterface
     */
    protected $securityContext;

    /**
     * @var Pim\Bundle\TranslationBundle\EventListener\AddLocaleListener
     */
    protected $translatableListener;

    /**
     * @var Pim\Bundle\CatalogBundle\Manager\ProductManager
     */
    protected $productManager;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->translatableListener = $this->getTranslatableListenerMock();
        $this->securityContext = $this->getSecurityContextMock();
        $this->productManager = $this->getProductManagerMock();

        $this->target = new UserContextListener(
            $this->securityContext,
            $this->translatableListener,
            $this->productManager
        );
    }

    /**
     * @return Pim\Bundle\TranslationBundle\EventListener\AddLocaleListener
     */
    protected function getTranslatableListenerMock()
    {
        return $this->getMock('Pim\Bundle\TranslationBundle\EventListener\AddLocaleListener');
    }

    /**
     * @return Symfony\Component\Security\Core\SecurityContextInterface
     */
    protected function getSecurityContextMock()
    {
        return $this->getMock(
            'Symfony\Component\Security\Core\SecurityContextInterface',
            array('getToken', 'setToken', 'isGranted')
        );
    }

    /**
     * Get product manager
     *
     * @return Pim\Bundle\CatalogBundle\Manager\ProductManager
     */
    protected function getProductManagerMock()
    {
        $productManager = $this->getMockBuilder('Pim\Bundle\CatalogBundle\Manager\ProductManager')
                               ->setMethods(array('setLocale', 'setScope'))
                               ->disableOriginalConstructor()
                               ->getMock();

        $productManager->expects($this->any())
                       ->method('setLocale')
                       ->will($this->returnValue($productManager));

        $productManager->expects($this->any())
                       ->method('setScope')
                       ->will($this->returnValue($productManager));

        return $productManager;
    }

    /**
     * @param integer $type
     *
     * @return Symfony\Component\HttpKernel\Event\GetResponseEvent
     */
    protected function getGetResponseEventMock($type = HttpKernel::MASTER_REQUEST)
    {
        $event = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\GetResponseEvent')
                      ->setMethods(array('getRequestType', 'getRequest'))
                      ->disableOriginalConstructor()
                      ->getMock();

        $event->expects($this->any())
              ->method('getRequestType')
              ->will($this->returnValue($type));

        $event->expects($this->any())
              ->method('getRequest')
              ->will($this->returnValue($this->getHttpRequest()));

        return $event;
    }

    /**
     * Create Request mock
     *
     * @return \Symfony\Component\HttpFoundation\Request
     */
    protected function getHttpRequest()
    {
        return new Request(array('dataLocale' => 'en_US', 'dataScope' => 'ecommerce'));
    }

    /**
     * Get Token mock
     *
     * @param Symfony\Component\Security\Core\User\UserInterface $user
     *
     * @return Symfony\Component\Security\Core\Authentication\Token\TokenInterface
     */
    protected function getTokenMock($user)
    {
        $token = $this->getMock(
            'Symfony\Component\Security\Core\Authentication\Token\TokenInterface',
            array(
                'getUser', 'setUser', '__toString', 'getRoles', 'getCredentials', 'setUsername', 'getUsername',
                'isAuthenticated', 'setAuthenticated', 'eraseCredentials', 'getAttributes', 'setAttributes',
                'hasAttribute', 'getAttribute', 'setAttribute', 'serialize', 'unserialize'
            )
        );

        $token->expects($this->any())
              ->method('getUser')
              ->will($this->returnValue($user));

        return $token;
    }

    /**
     * Get User mock
     *
     * @param string $catalogLocale
     *
     * @return Symfony\Component\Security\Core\User\UserInterface
     */
    private function getUserMock($catalogLocale)
    {
        $user = $this->getMock(
            'Symfony\Component\Security\Core\User\UserInterface',
            array('getValue', 'getRoles', 'getPassword', 'getSalt', 'getUsername', 'eraseCredentials')
        );

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

    /**
     * Test class instanciated
     */
    public function testEventSubscriberInstance()
    {
        $this->assertInstanceOf('Symfony\Component\EventDispatcher\EventSubscriberInterface', $this->target);
    }

    /**
     * Test related method
     */
    public function testGetSubscribedEvent()
    {
        $this->assertEquals(
            array('kernel.request' => 'onKernelRequest'),
            $this->target->getSubscribedEvents()
        );
    }

    /**
     * Test related method
     */
    public function testOnKernelRequest()
    {
        $user = $this->getUserMock('en_US');
        $token = $this->getTokenMock($user);
        $this->securityContext
             ->expects($this->any())
             ->method('getToken')
             ->will($this->returnValue($token));

        $this->translatableListener
             ->expects($this->once())
             ->method('setLocale')
             ->with($this->equalTo('en_US'));

        $this->target->onKernelRequest($this->getGetResponseEventMock());
    }

    /**
     * Test related method without token
     */
    public function testOnKernelRequestWithNoToken()
    {
        $this->securityContext
             ->expects($this->any())
             ->method('getToken')
             ->will($this->returnValue(null));

        $this->translatableListener
             ->expects($this->never())
             ->method('setLocale');

        $this->target->onKernelRequest($this->getGetResponseEventMock());
    }

    /**
     * Test related method without user
     */
    public function testOnKernelRequestWithNoUser()
    {
        $token = $this->getTokenMock(null);
        $this->securityContext
             ->expects($this->any())
             ->method('getToken')
             ->will($this->returnValue($token));

        $this->translatableListener
             ->expects($this->never())
             ->method('setLocale');

        $this->target->onKernelRequest($this->getGetResponseEventMock());
    }

    /**
     * Test related method with subrequest
     */
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
             ->method('setLocale');

        $this->target->onKernelRequest($this->getGetResponseEventMock(HttpKernel::SUB_REQUEST));
    }
}
