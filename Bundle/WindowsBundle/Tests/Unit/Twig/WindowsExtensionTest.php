<?php

namespace Oro\Bundle\WindowsBundle\Tests\Twig;

use Symfony\Component\Security\Core\SecurityContextInterface;

use Twig_Environment;
use Doctrine\ORM\EntityManager;
use Oro\Bundle\WindowsBundle\Twig\WindowsExtension;

class WindowsExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var WindowsExtension
     */
    protected $extension;

    /**
     * @var Twig_Environment $environment
     */
    protected $environment;

    /**
     * @var SecurityContextInterface
     */
    protected $securityContext;

    /**
     * @var EntityManager
     */
    protected $em;

    protected function setUp()
    {
        $this->environment = $this->getMockBuilder('Twig_Environment')
            ->disableOriginalConstructor()
            ->getMock();
        $this->securityContext = $this->getMockBuilder('Symfony\Component\Security\Core\SecurityContextInterface')
            ->getMock();
        $this->em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $this->extension = new WindowsExtension($this->securityContext, $this->em);
    }

    public function testGetFunctions()
    {
        $functions = $this->extension->getFunctions();
        $this->assertArrayHasKey('oro_windows_restore', $functions);
        $this->assertInstanceOf('Twig_Function_Method', $functions['oro_windows_restore']);
        $this->assertAttributeEquals('render', 'method', $functions['oro_windows_restore']);
    }

    public function testGetName()
    {
        $this->assertEquals('oro_windows', $this->extension->getName());
    }

    public function testRenderNoUser()
    {
        $this->assertEmpty($this->extension->render($this->environment));
    }

    /**
     * @dataProvider renderDataProvider
     * @param string $widgetUrl
     * @param string $widgetType
     * @param string $expectedWidgetUrl
     */
    public function testRender($widgetUrl, $widgetType, $expectedWidgetUrl)
    {
        $user = $this->getMock('stdClass');

        $token = $this->getMockBuilder('stdClass')
            ->setMethods(array('getUser'))
            ->getMock();
        $token->expects($this->once())
            ->method('getUser')
            ->will($this->returnValue($user));
        $this->securityContext->expects($this->once())
            ->method('getToken')
            ->will($this->returnValue($token));

        $normalStateData = array('cleanUrl' => $widgetUrl, 'type' => $widgetType);
        $normalState = $this->getStateMock(1, $normalStateData);
        $badState = $this->getStateMock(2, null);
        $states = array($normalState, $badState);
        $repository = $this->getMock('Doctrine\Common\Persistence\ObjectRepository');
        $repository->expects($this->once())
            ->method('findBy')
            ->with(array('user' => $user))
            ->will($this->returnValue($states));
        $this->em->expects($this->once())
            ->method('getRepository')
            ->with('OroWindowsBundle:WindowsState')
            ->will($this->returnValue($repository));

        $this->em->expects($this->once())
            ->method('remove')
            ->with($badState);
        $this->em->expects($this->once())
            ->method('flush');

        $output = 'RENDERED';
        $expectedStates = array('cleanUrl' => $expectedWidgetUrl, 'type' => $widgetType);
        $this->environment->expects($this->once())
            ->method('render')
            ->with("OroWindowsBundle::states.html.twig", array("states" => array(1 => $expectedStates)))
            ->will($this->returnValue($output));

        $this->assertEquals($output, $this->extension->render($this->environment));
    }

    /**
     * @return array
     */
    public function renderDataProvider()
    {
        return array(
            'url_without_parameters'
                => array('/user/create', 'test', '/user/create?_widgetContainer=test'),
            'url_with_parameters'
                => array('/user/create?id=1', 'test', '/user/create?id=1&_widgetContainer=test'),
            'url_with_parameters_and_fragment'
                => array('/user/create?id=1#group=date', 'test', '/user/create?id=1&_widgetContainer=test#group=date'),
        );
    }

    protected function getStateMock($id, $data)
    {
        $state = $this->getMock('Oro\Bundle\WindowsBundle\Entity\WindowsState');
        $state->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));
        $state->expects($this->any())
            ->method('getId')
            ->will($this->returnValue($id));
        return $state;
    }
}
