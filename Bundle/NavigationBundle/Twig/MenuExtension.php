<?php

namespace Oro\Bundle\NavigationBundle\Twig;

use Knp\Menu\ItemInterface;
use Knp\Menu\Twig\Helper;
use Knp\Menu\Provider\MenuProviderInterface;

use Oro\Bundle\NavigationBundle\Menu\BreadcrumbManager;

use Symfony\Component\DependencyInjection\ContainerInterface;

class MenuExtension extends \Twig_Extension
{
    const MENU_NAME = 'oro_menu';

    const BREADCRUMBS_TEMPLATE = 'OroNavigationBundle:Menu:breadcrumbs.html.twig';

    /**
     * @var Helper $helper
     */
    private $helper;

    /**
     * @var MenuProviderInterface $provider
     */
    private $provider;

    /**
     * @var ContainerInterface $container
     */
    protected $container;

    /**
     * @var BreadcrumbManager
     */
    protected $breadcrumbManager;

    /**
     * @param Helper $helper
     * @param MenuProviderInterface $provider
     * @param BreadcrumbManager $breadcrumbManager
     * @param ContainerInterface $container
     */
    public function __construct(
        Helper $helper,
        MenuProviderInterface $provider,
        BreadcrumbManager $breadcrumbManager,
        ContainerInterface $container
    ) {
        $this->helper = $helper;
        $this->provider = $provider;
        $this->breadcrumbManager = $breadcrumbManager;
        $this->container = $container;
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return array(
            'oro_menu_render' => new \Twig_Function_Method($this, 'render', array('is_safe' => array('html'))),
            'oro_menu_get' => new \Twig_Function_Method($this, 'getMenu'),
            'oro_breadcrumbs' => new \Twig_Function_Method(
                $this,
                'renderBreadCrumbs',
                array(
                    'is_safe' => array('html'),
                    'needs_environment' => true
                )
            )
        );
    }

    /**
     * Renders a menu with the specified renderer.
     *
     * @param ItemInterface|string|array $menu
     * @param array                      $options
     * @param string                     $renderer
     *
     * @throws \InvalidArgumentException
     * @return string
     */
    public function render($menu, array $options = array(), $renderer = null)
    {
        if (!$menu instanceof ItemInterface) {
            $path = array();
            if (is_array($menu)) {
                if (empty($menu)) {
                    throw new \InvalidArgumentException('The array cannot be empty');
                }
                $path = $menu;
                $menu = array_shift($path);
            }

            $menu = $this->getMenu($menu, $path, $options);
        }

        $menuType = $menu->getExtra('type');
        if (!empty($menuType)) {
            $menuConfig = $this->container->getParameter('oro_menu_config');
            if (!empty($menuConfig['templates'][$menuType])) {
                // rewrite config options with args
                $options = array_replace_recursive($menuConfig['templates'][$menuType], $options);
            }
        }

        return $this->helper->render($menu, $options, $renderer);
    }

    /**
     * Render breadcrumbs for menu
     *
     * @param \Twig_Environment $environment
     * @param string $menuName
     * @param bool $useDecorators
     * @return null|string
     */
    public function renderBreadCrumbs(\Twig_Environment $environment, $menuName, $useDecorators = true)
    {
        if ($breadcrumbs = $this->breadcrumbManager->getBreadcrumbs($menuName, $useDecorators)) {
            $template = $environment->loadTemplate(self::BREADCRUMBS_TEMPLATE);

            return $template->render(
                array(
                    'breadcrumbs' => $breadcrumbs,
                    'useDecorators' => $useDecorators
                )
            );
        }

        return null;
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return self::MENU_NAME;
    }

    /**
     * Retrieves item in the menu, eventually using the menu provider.
     *
     * @param ItemInterface|string $menu
     * @param array                $path
     * @param array                $options
     *
     * @return ItemInterface
     *
     * @throws \LogicException
     * @throws \InvalidArgumentException when the path is invalid
     */
    public function getMenu($menu, array $path = array(), array $options = array())
    {
        if (!$menu instanceof ItemInterface) {
            $menu = $this->provider->get((string) $menu, $options);
        }

        foreach ($path as $child) {
            $menu = $menu->getChild($child);
            if (null === $menu) {
                throw new \InvalidArgumentException(sprintf('The menu has no child named "%s"', $child));
            }
        }

        return $menu;
    }
}
