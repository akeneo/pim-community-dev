<?php
namespace Oro\Bundle\UserBundle\Tests\Unit\Acl;

use CG\Proxy\MethodInvocation;

use Oro\Bundle\UserBundle\Acl\AclInterceptor;
use Oro\Bundle\UserBundle\Tests\Unit\Fixture\Controller\MainTestController;
use Oro\Bundle\SecurityBundle\Annotation\Acl;

class AclInterceptorTest extends \PHPUnit_Framework_TestCase
{
    private $securityContext;
    private $logger;
    private $container;
    private $annotationReader;
    private $decisionManager;
    private $token;
    private $aclManager;
    private $request;
    private $testMethodInvocation;
    private $inceptor;
    private $requestAttributes;
    private $aclConfigReader;

    public function setUp()
    {
        $this->securityContext = $this->getMockForAbstractClass(
            'Symfony\Component\Security\Core\SecurityContextInterface'
        );

        $this->logger = $this->getMockForAbstractClass(
            'Symfony\Component\HttpKernel\Log\LoggerInterface'
        );

        $this->container = $this->getMockForAbstractClass(
            'Symfony\Component\DependencyInjection\ContainerInterface'
        );

        $this->annotationReader = $this->getMock(
            'Doctrine\Common\Annotations\AnnotationReader'
        );

        $this->aclConfigReader = new \Oro\Bundle\UserBundle\Acl\ResourceReader\ConfigReader(
            array(
                 'Oro\Bundle\UserBundle\Tests\Unit\Fixture\FixtureBundle'
            )
        );

        $this->decisionManager = $this->getMockBuilder(
            'Symfony\Component\Security\Core\Authorization\AccessDecisionManager'
        )
            ->disableOriginalConstructor()
            ->getMock();

        $this->token = $this->getMockForAbstractClass(
            'Symfony\Component\Security\Core\Authentication\Token\TokenInterface'
        );

        $this->aclManager = $this->getMockBuilder('Oro\Bundle\UserBundle\Acl\Manager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->request = $this->getMock('Symfony\Component\HttpFoundation\Request');
        $this->requestAttributes = $this->getMock('Symfony\Component\HttpFoundation\ParameterBag');

        $this->request->attributes = $this->requestAttributes;

        $this->securityContext
            ->expects($this->once())
            ->method('getToken')
            ->will($this->returnValue($this->token));

        $params = array(
            'security.context'                 => $this->securityContext,
            'logger'                           => $this->logger,
            'annotation_reader'                => $this->annotationReader,
            'security.access.decision_manager' => $this->decisionManager,
            'oro_user.acl_manager'             => $this->aclManager,
            'request'                          => $this->request,
            'oro_user.acl_config_reader'       => $this->aclConfigReader
        );

        $this->container->expects($this->any())
            ->method('get')
            ->with(
                $this->logicalOr(
                    $this->equalTo('security.context'),
                    $this->equalTo('logger'),
                    $this->equalTo('annotation_reader'),
                    $this->equalTo('security.access.decision_manager'),
                    $this->equalTo('oro_user.acl_manager'),
                    $this->equalTo('request'),
                    $this->equalTo('oro_user.acl_config_reader')
                )
            )
            ->will(
                $this->returnCallback(
                    function ($param) use (&$params) {
                        return $params[$param];
                    }
                )
            );

        $object = new  MainTestController();
        $this->testMethodInvocation = new MethodInvocation(
            new \ReflectionMethod($object, 'test1Action'), $object, array(), array()
        );

        $this->inceptor = new AclInterceptor($this->container);
    }

    public function testNoAccessOnInternalRouteHtml()
    {
        $this->decisionManager->expects($this->once())
            ->method('decide')
            ->will($this->returnValue(false));

        $this->requestAttributes->expects($this->once())
            ->method('get')
            ->will($this->returnValue(null));

        $this->aclManager
            ->expects($this->once())
            ->method('getAclRolesWithoutTree')
            ->will($this->returnValue(array('TEST_ROLE', 'ANOTHER_ROLE')));

        $result = $this->inceptor->intercept($this->testMethodInvocation);
        $this->assertEquals('Symfony\Component\HttpFoundation\Response', get_class($result));
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @expectedExceptionMessage Access denied.
     */
    public function testNoAccessDefault()
    {
        $this->decisionManager->expects($this->once())
            ->method('decide')
            ->will($this->returnValue(false));

        $this->requestAttributes->expects($this->once())
            ->method('get')
            ->will($this->returnValue('test_route'));

        $this->aclManager
            ->expects($this->once())
            ->method('getAclRolesWithoutTree')
            ->will($this->returnValue(array('TEST_ROLE', 'ANOTHER_ROLE')));

        $this->inceptor->intercept($this->testMethodInvocation);
    }

    public function testAccess()
    {
        $this->decisionManager->expects($this->once())
            ->method('decide')
            ->will($this->returnValue(true));

        $this->aclManager
            ->expects($this->once())
            ->method('getAclRolesWithoutTree')
            ->will($this->returnValue(array('TEST_ROLE', 'ANOTHER_ROLE')));

        $this->inceptor->intercept($this->testMethodInvocation);
    }

    public function testNoAccessWithRoles()
    {
        $annotation = new Acl(
            array(
                 'id'          => 'testAcl',
                 'name'        => 'test name',
                 'description' => 'test description'
            )
        );

        $this->annotationReader->expects($this->once())
            ->method('getMethodAnnotation')
            ->will($this->returnValue($annotation));

        $this->decisionManager->expects($this->once())
            ->method('decide')
            ->will($this->returnValue(false));

        $this->requestAttributes->expects($this->once())
            ->method('get')
            ->will($this->returnValue('test_route'));

        $this->aclManager
            ->expects($this->once())
            ->method('getAclRoles')
            ->will($this->returnValue(array('TEST_ROLE')));

        $this->setExpectedException('RuntimeException');
        $this->inceptor->intercept($this->testMethodInvocation);
    }
}
