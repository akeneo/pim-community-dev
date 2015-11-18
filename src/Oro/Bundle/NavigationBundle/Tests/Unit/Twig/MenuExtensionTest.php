<?php

namespace Oro\Bundle\NavigationBundle\Tests\Unit\Twig;

use Knp\Menu\Twig\Helper;
use Oro\Bundle\NavigationBundle\Menu\AclAwareMenuFactory;
use Oro\Bundle\NavigationBundle\Menu\ConfigurationBuilder;
use Oro\Bundle\NavigationBundle\Provider\BuilderChainProvider;
use Oro\Bundle\NavigationBundle\Twig\MenuExtension;
use Oro\Bundle\UserBundle\Acl\Manager;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Routing\RouterInterface;

class MenuExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Container $container
     */
    protected $container;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Helper $helper
     */
    protected $helper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ConfigurationBuilder $builder
     */
    protected $builder;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|AclAwareMenuFactory $factory
     */
    protected $factory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $breadcrumbManager;

    /**
     * @var MenuExtension $menuExtension
     */
    protected $menuExtension;

    protected function setUp()
    {
        $this->container = new Container();

        $this->breadcrumbManager = $this->getMockBuilder('Oro\Bundle\NavigationBundle\Menu\BreadcrumbManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->helper = $this->getMockBuilder('Knp\Menu\Twig\Helper')
            ->disableOriginalConstructor()
            ->setMethods(['render'])
            ->getMock();

        $this->factory = $this->getMockBuilder('Knp\Menu\MenuFactory')
            ->setMethods(['getRouteInfo', 'processRoute'])
            ->getMock();

        $this->factory->expects($this->any())
            ->method('getRouteInfo')
            ->will($this->returnValue(false));

        $this->factory->expects($this->any())
            ->method('processRoute')
            ->will($this->returnSelf());

        /** @var $eventDispatcher EventDispatcherInterface */
        $eventDispatcher = $this->getMockBuilder('Symfony\Component\EventDispatcher\EventDispatcherInterface')
            ->getMock();
        $provider = new BuilderChainProvider($this->factory, $eventDispatcher);

        $this->builder = new ConfigurationBuilder();
        $this->builder->setContainer($this->container);
        $provider->addBuilder($this->builder);

        $this->menuExtension = new MenuExtension($this->helper, $provider, $this->breadcrumbManager, $this->container);
    }

    public function testGetFunctions()
    {
        $functions = $this->menuExtension->getFunctions();
        $this->assertArrayHasKey('oro_menu_render', $functions);
        $this->assertInstanceOf('Twig_Function_Method', $functions['oro_menu_render']);
        $this->assertAttributeEquals('render', 'method', $functions['oro_menu_render']);

        $this->assertArrayHasKey('oro_menu_get', $functions);
        $this->assertInstanceOf('Twig_Function_Method', $functions['oro_menu_get']);
        $this->assertAttributeEquals('getMenu', 'method', $functions['oro_menu_get']);
    }

    public function testGetName()
    {
        $this->assertEquals(MenuExtension::MENU_NAME, $this->menuExtension->getName());
    }

    /**
     * @dataProvider menuStructureProvider
     * @param $menuConfig
     * @param $menu
     * @param array $options
     * @param $renderer
     * @return void
     */
    public function testBuild($menuConfig, $menu, $options, $renderer)
    {
        $this->container->setParameter('oro_menu_config', $menuConfig);

        $this->helper->expects($this->once())
            ->method('render')
            ->with(
                $this->containsOnlyInstancesOf('Knp\Menu\MenuItem'),
                $this->equalTo(['template' => $menuConfig['templates']['navbar']['template']]),
                $this->equalTo(null)
            )
            ->will($this->returnValue('menu'));

        $this->menuExtension->render($menu, $options, $renderer);
    }

    /**
     * @return array
     */
    protected function getMenuConfigYamlArray()
    {
        return [
                'templates' => [
                    'navbar' => [
                        'template' => 'OroNavigationBundle:Menu:navbar.html.twig'
                    ],
                    'dropdown' => [
                        'template' => 'OroNavigationBundle:Menu:dropdown.html.twig'
                    ]
                ],
                'items' => [
                    'homepage' => [
                        'name'                => 'Home page 2',
                        'label'               => 'Home page title',
                        'route'               => 'oro_menu_index',
                        'translateDomain'     => 'SomeBundle',
                        'translateParameters' => [],
                        'routeParameters'     => [],
                        'extras'              => []
                    ],
                    'user_registration_register' => [
                        'route'               => 'oro_menu_submenu',
                        'translateDomain'     => 'SomeBundle',
                        'translateParameters' => [],
                        'routeParameters'     => [],
                        'extras'              => []
                    ],
                    'user_user_show' => [
                        'translateDomain'     => 'SomeBundle',
                        'translateParameters' => [],
                        'routeParameters'     => [],
                        'extras'              => []
                    ],
                ],
                'tree' => [
                    'navbar' => [
                        'type'   => 'navbar',
                        'extras' => [
                            'brand'     => 'Oro',
                            'brandLink' => '/'
                        ],
                        'children' => [
                            'user_user_show' => [
                                'position' => '10',
                                'children' => [
                                    'user_registration_register' => [
                                        'children' => []
                                    ]
                                ]
                            ],
                            'homepage' => [
                                'position' => 7,
                                'children' => []
                            ]
                        ]
                    ]
                ]
        ];
    }

    /**
     * @return array
     */
    public function menuStructureProvider()
    {
        return [
            'full_menu' => [$this->getMenuConfigYamlArray(), 'navbar', [], null]
        ];
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The menu has no child named "some_element"
     */
    public function testBuildChildExistsException()
    {
        $menuConfig = $this->getMenuConfigYamlArray();
        $this->container->setParameter('oro_menu_config', $menuConfig);
        $this->menuExtension->render(['navbar', 'some_element']);
    }

    /**
     */
    public function testBuildChildExists()
    {
        $menuConfig = $this->getMenuConfigYamlArray();
        $this->helper->expects($this->once())
            ->method('render')
            ->with(
                $this->containsOnlyInstancesOf('Knp\Menu\MenuItem'),
                $this->equalTo([]),
                $this->equalTo(null)
            )
            ->will($this->returnValue('menu'));

        $this->container->setParameter('oro_menu_config', $menuConfig);
        $this->menuExtension->render(['navbar', 'user_user_show']);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The array cannot be empty
     */
    public function testBuildEmptyNameException()
    {
        $menuConfig = $this->getMenuConfigYamlArray();
        $this->container->setParameter('oro_menu_config', $menuConfig);
        $this->menuExtension->render([]);
    }

    /**
     */
    public function testBuildWithOptionsAndRenderer()
    {
        $menuConfig = $this->getMenuConfigYamlArray();
        $this->helper->expects($this->once())
            ->method('render')
            ->with(
                $this->containsOnlyInstancesOf('Knp\Menu\MenuItem'),
                $this->equalTo(['type' => 'some_menu']),
                $this->equalTo('some_renderer')
            )
            ->will($this->returnValue('menu'));

        $this->container->setParameter('oro_menu_config', $menuConfig);
        $this->menuExtension
            ->render(['navbar', 'user_user_show'], ['type' => 'some_menu'], 'some_renderer');
    }

    public function testRenderBreadCrumbs()
    {
        $environment = $this->getMockBuilder('\Twig_Environment')
            ->disableOriginalConstructor()
            ->getMock();

        $template = $this->getMockBuilder('\Twig_TemplateInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->breadcrumbManager->expects($this->once())
            ->method('getBreadcrumbs')
            ->will($this->returnValue(['test-breadcrumb']));

        $environment->expects($this->once())
            ->method('loadTemplate')
            ->will($this->returnValue($template));

        $template->expects($this->once())
            ->method('render')
            ->with(
                [
                    'breadcrumbs' => [
                        'test-breadcrumb'
                    ],
                    'useDecorators' => true
                ]
            );
        ;
        $this->menuExtension->renderBreadCrumbs($environment, 'test_menu');
    }

    public function testWrongBredcrumbs()
    {
        $environment = $this->getMockBuilder('\Twig_Environment')
            ->disableOriginalConstructor()
            ->getMock();

        $this->breadcrumbManager->expects($this->once())
            ->method('getBreadcrumbs')
            ->will($this->returnValue(null));

        $this->assertNull($this->menuExtension->renderBreadCrumbs($environment, 'test_menu'));
    }
}
