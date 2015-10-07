<?php
namespace Oro\Bundle\NavigationBundle\Tests\Unit\Event;

use Pim\Bundle\UserBundle\Entity\User;
use Pim\Bundle\UserBundle\Entity\UserInterface;
use Oro\Bundle\NavigationBundle\Entity\NavigationHistoryItem;
use Oro\Bundle\NavigationBundle\Event\ResponseHistoryListener;
use Oro\Bundle\NavigationBundle\Provider\TitleService;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class ResponseHistoryListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @var \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @var ResponseHistoryListener
     */
    protected $listener;

    /**
     * @var NavigationHistoryItem
     */
    protected $item;

    /**
     * @var \Oro\Bundle\NavigationBundle\Entity\Builder\ItemFactory
     */
    protected $factory;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var TitleService
     */
    protected $titleService;

    /**
     * @var string
     */
    protected $serializedTitle;

    public function setUp()
    {
        $this->factory = $this->getMock('Oro\Bundle\NavigationBundle\Entity\Builder\ItemFactory');
        $this->tokenStorage = $this->getMock('Symfony\Component\Security\Core\TokenStorageInterface');

        $user = new User();
        $user->setEmail('some@email.com');

        $token = $this->getMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $token->expects($this->exactly(2))
            ->method('getUser')
            ->will($this->returnValue($user));

        $this->tokenStorage->expects($this->any())
            ->method('getToken')
            ->will($this->returnValue($token));

        $this->item = $this->getMock('Oro\Bundle\NavigationBundle\Entity\NavigationHistoryItem');

        $this->serializedTitle = json_encode(array('titleTemplate' => 'Test title template'));
    }

    public function testOnResponse()
    {
        $response = $this->getResponse();

        $repository = $this->getDefaultRepositoryMock($this->item);
        $em = $this->getEntityManager($repository);

        $listener = $this->getListener($this->factory, $this->tokenStorage, $em);
        $listener->onResponse($this->getEventMock($this->getRequest(), $response));
    }

    public function testTitle()
    {
        $this->item->expects($this->once())
            ->method('setTitle')
            ->with($this->equalTo($this->serializedTitle));

        $response = $this->getResponse();
        $repository = $this->getDefaultRepositoryMock($this->item);
        $em = $this->getEntityManager($repository);

        $listener = $this->getListener($this->factory, $this->tokenStorage, $em);
        $listener->onResponse($this->getEventMock($this->getRequest(), $response));
    }

    public function testNewItem()
    {
        $user = new User();
        $user->setEmail('some@email.com');

        $this->factory->expects($this->once())
            ->method('createItem')
            ->will($this->returnValue($this->item));

        $repository = $this->getDefaultRepositoryMock(null);
        $em = $this->getEntityManager($repository);

        $listener = $this->getListener($this->factory, $this->tokenStorage, $em);
        $response = $this->getResponse();

        $listener->onResponse($this->getEventMock($this->getRequest(), $response));
    }

    public function testNotMasterRequest()
    {
        $event = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\FilterResponseEvent')
            ->disableOriginalConstructor()
            ->getMock();

        $event->expects($this->never())
            ->method('getRequest');
        $event->expects($this->never())
            ->method('getResponse');
        $event->expects($this->once())
            ->method('getRequestType')
            ->will($this->returnValue(HttpKernelInterface::SUB_REQUEST));

        $em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $em->expects($this->never())
            ->method('getRepository');

        $titleService = $this->getMock('Oro\Bundle\NavigationBundle\Provider\TitleServiceInterface');

        $listener = new ResponseHistoryListener($this->factory, $this->tokenStorage, $em, $titleService);
        $listener->onResponse($event);
    }

    /**
     * Get the mock of the GetResponseEvent and FilterResponseEvent.
     *
     * @param \Symfony\Component\HttpFoundation\Request       $request
     * @param null|\Symfony\Component\HttpFoundation\Response $response
     * @param string                                          $type
     *
     * @return mixed
     */
    private function getEventMock($request, $response, $type = 'Symfony\Component\HttpKernel\Event\FilterResponseEvent')
    {
        $event = $this->getMockBuilder($type)
            ->disableOriginalConstructor()
            ->getMock();
        $event->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($request));
        $event->expects($this->any())
            ->method('getRequestType')
            ->will($this->returnValue(HttpKernelInterface::MASTER_REQUEST));

        $event->expects($this->any())
            ->method('getResponse')
            ->will($this->returnValue($response));

        return $event;
    }

    /**
     * Creates request mock object
     *
     * @return Request
     */
    private function getRequest()
    {
        $this->request = $this->getMock('Symfony\Component\HttpFoundation\Request');

        $this->request->expects($this->once())
            ->method('getRequestFormat')
            ->will($this->returnValue('html'));

        $this->request->expects($this->once())
            ->method('getMethod')
            ->will($this->returnValue('GET'));

        $this->request->expects($this->once())
            ->method('get')
            ->with('_route')
            ->will($this->returnValue('test_route'));

        return $this->request;
    }

    /**
     * Creates response object mock
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getResponse()
    {
        $response = $this->getMock('Symfony\Component\HttpFoundation\Response');

        $response->expects($this->once())
            ->method('getStatusCode')
            ->will($this->returnValue(200));

        return $response;
    }

    public function getTitleService()
    {

        $this->titleService = $this->getMock('Oro\Bundle\NavigationBundle\Provider\TitleServiceInterface');
        $this->titleService->expects($this->once())
            ->method('getSerialized')
            ->will($this->returnValue($this->serializedTitle));

        return $this->titleService;
    }

    /**
     * @param  \Oro\Bundle\NavigationBundle\Entity\Builder\ItemFactory   $factory
     * @param  \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface $tokenStorage
     * @param  \Doctrine\ORM\EntityManager                               $entityManager
     * @return ResponseHistoryListener
     */
    private function getListener($factory, $tokenStorage, $entityManager)
    {
        return new ResponseHistoryListener($factory, $tokenStorage, $entityManager, $this->getTitleService());
    }

    /**
     * Returns EntityManager
     *
     * @param  \Oro\Bundle\NavigationBundle\Entity\Repository\HistoryItemRepository $repositoryMock
     * @return \Doctrine\ORM\EntityManager                                          $entityManager
     */
    private function getEntityManager($repositoryMock)
    {
        $this->em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->em->expects($this->once())
            ->method('getRepository')
            ->with($this->equalTo('Oro\Bundle\NavigationBundle\Entity\NavigationHistoryItem'))
            ->will($this->returnValue($repositoryMock));

        return $this->em;
    }

    /**
     * Prepare repository mock
     *
     * @param  mixed                                    $returnValue
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getDefaultRepositoryMock($returnValue)
    {
        $repository = $this->getMockBuilder('Oro\Bundle\NavigationBundle\Entity\Repository\HistoryItemRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $repository->expects($this->once())
            ->method('findOneBy')
            ->will($this->returnValue($returnValue));

        return $repository;
    }
}
