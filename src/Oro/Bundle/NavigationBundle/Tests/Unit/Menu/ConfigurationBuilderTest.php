<?php

namespace Oro\Bundle\NavigationBundle\Tests\Unit\Menu;

use Oro\Bundle\NavigationBundle\Menu\AclAwareMenuFactory;
use Oro\Bundle\NavigationBundle\Menu\ConfigurationBuilder;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Routing\RouterInterface;
use Oro\Bundle\UserBundle\Acl\Manager;
use Knp\Menu\MenuItem;

class ConfigurationBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Container $container
     */
    protected $container;

    /**
     * @var ConfigurationBuilder $configurationBuilder
     */
    protected $configurationBuilder;

    /**
     * @var AclAwareMenuFactory
     */
    protected $factory;

    protected function setUp()
    {
        $this->container = new Container();
        $this->configurationBuilder = new ConfigurationBuilder();

        $this->factory = $this->getMockBuilder('Knp\Menu\MenuFactory')
            ->setMethods(array('getRouteInfo', 'processRoute'))
            ->getMock();

        $this->factory->expects($this->any())
            ->method('getRouteInfo')
            ->will($this->returnValue(false));

        $this->factory->expects($this->any())
            ->method('processRoute')
            ->will($this->returnSelf());
    }

    public function testSetContainer()
    {
        $this->configurationBuilder->setContainer($this->container);
        $this->assertAttributeEquals($this->container, 'container', $this->configurationBuilder);
    }

    /**
     * @dataProvider menuStructureProvider
     * @param array $options
     */
    public function testBuild($options)
    {
        $this->container->setParameter('oro_menu_config', $options);
        $this->configurationBuilder->setContainer($this->container);

        $menu = new MenuItem('navbar', $this->factory);
        $this->configurationBuilder->build($menu, array(), 'navbar');

        $this->assertCount(2, $menu->getChildren());
        $this->assertEquals($options['tree']['navbar']['type'], $menu->getExtra('type'));
        $this->assertCount(
            count($options['tree']['navbar']['children']['user_user_show']['children']),
            $menu->getChild('user_user_show')
        );
        $this->assertEquals('user_user_show', $menu->getChild('user_user_show')->getName());
    }

    /**
     * @return array
     */
    public function menuStructureProvider()
    {
        return array(
            'full_menu' => array(array(
                'templates' => array(
                    'navbar' => array(
                        'template' => 'OroNavigationBundle:Menu:navbar.html.twig'
                        ),
                    'dropdown' => array(
                        'template' => 'OroNavigationBundle:Menu:dropdown.html.twig'
                    )
                ),
                'items' => array(
                    'homepage' => array(
                        'name' => 'Home page 2',
                        'label' => 'Home page title',
                        'route' => 'oro_menu_index',
                        'translateDomain' => 'SomeBundle',
                        'translateParameters' => array(),
                        'routeParameters' => array(),
                        'extras' => array()
                    ),
                    'user_registration_register' => array(
                        'route' => 'oro_menu_submenu',
                        'translateDomain' => 'SomeBundle',
                        'translateParameters' => array(),
                        'routeParameters' => array(),
                        'extras' => array()
                    ),
                    'user_user_show' => array(
                        'translateDomain' => 'SomeBundle',
                        'translateParameters' => array(),
                        'routeParameters' => array(),
                        'extras' => array()
                    ),
                ),
                'tree' => array(
                    'navbar' => array(
                        'type' => 'navbar',
                        'extras' => array(
                            'brand' => 'Oro',
                            'brandLink' => '/'
                        ),
                        'children' => array(
                            'user_user_show' => array(
                                'position' => '10',
                                'children' => array(
                                    'user_registration_register' => array(
                                        'children' => array()
                                    )
                                )
                            ),
                            'homepage' => array(
                                'position' => 7,
                                'children' => array()
                            )
                        )
                    )
                )
            ))
        );
    }
}
