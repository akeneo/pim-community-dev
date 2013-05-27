<?php

namespace Oro\Bundle\NavigationBundle\Tests\Unit\Command;

use Oro\Bundle\NavigationBundle\Command\TitleIndexUpdateCommand;

class TitleIndexUpdateCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TitleIndexUpdateCommand
     */
    private $command;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $container;

    public function setUp()
    {
        $this->command = new TitleIndexUpdateCommand();

        $this->container = $this->getMock('Symfony\Component\DependencyInjection\ContainerBuilder');
        $this->command->setContainer($this->container);
    }

    public function testConfiguration()
    {
        $this->command->configure();

        $this->assertNotEmpty($this->command->getDescription());
        $this->assertNotEmpty($this->command->getName());
    }

    /**
     * @dataProvider provideMethod
     * @param string $data
     */
    public function testExecute($data)
    {
        $input = $this->getMock('Symfony\Component\Console\Input\InputInterface');
        $output = $this->getMock('Symfony\Component\Console\Output\OutputInterface');

        $route = $this->getMock('Symfony\Component\Routing\Route', array(), array('/user/show/{id}'));

        $route->expects($this->once())
            ->method('getRequirements')
            ->will($this->returnValue(array('_method' => $data)));

        $route->expects($this->once())
            ->method('getDefault')
            ->with('_controller')
            ->will($this->returnValue(''));

        $routerCollection = $this->getMock('Symfony\Component\Routing\RouteCollection');

        $routerCollection->expects($this->once())
            ->method('all')
            ->will($this->returnValue(array($route)));

        $router = $this->getMock('Symfony\Component\Routing\RouterInterface');

        $router->expects($this->once())
            ->method('getRouteCollection')
            ->will($this->returnValue($routerCollection));

        $titleService = $this->getMock('Oro\Bundle\NavigationBundle\Provider\TitleServiceInterface');
        $titleService->expects($this->once())
            ->method('update');

        $this->container->expects($this->at(0))
            ->method('get')
            ->with($this->equalTo('router'))
            ->will($this->returnValue($router));

        $this->container->expects($this->at(1))
            ->method('get')
            ->with($this->equalTo('oro_navigation.title_service'))
            ->will($this->returnValue($titleService));

        $this->command->execute($input, $output);
    }

    /**
     * Data provider
     *
     * @return array
     */
    public function provideMethod()
    {
        return array(
            array('GET'),
            array('ANY'),
            array(
                array('POST', 'GET')
            ),
        );
    }
}
