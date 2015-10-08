<?php
namespace Oro\Bundle\NavigationBundle\Menu;

use Knp\Menu\ItemInterface;
use Knp\Menu\Provider\MenuProviderInterface;
use Knp\Menu\Util\MenuManipulator;
use Knp\Menu\Matcher\Matcher;
use Symfony\Component\Routing\Router;

class BreadcrumbManager
{
    /**
     * @var Matcher
     */
    protected $matcher;

    /**
     * @var MenuProviderInterface
     */
    protected $provider;

    /**
     * @var Router
     */
    protected $router;

    /**
     * @param MenuProviderInterface $provider
     * @param Matcher $matcher
     * @param Router $router
     */
    public function __construct(MenuProviderInterface $provider, Matcher $matcher, Router $router)
    {
        $this->matcher = $matcher;
        $this->provider = $provider;
        $this->router = $router;
    }

    /**
     * Get breadcrumbs for current menu item
     *
     * @param $menuName
     * @param bool $isInverse
     * @return array
     */
    public function getBreadcrumbs($menuName, $isInverse = true)
    {
        $menu = $this->getMenu($menuName);
        $currentItem = $this->getCurrentMenuItem($menu);

        if ($currentItem) {

            return $this->getBreadcrumbArray($menuName, $currentItem, $isInverse);
        }
    }

    /**
     * Retrieves item in the menu, eventually using the menu provider.
     *
     * @param ItemInterface|string $menu
     * @param array $pathName
     * @param array $options
     *
     * @return ItemInterface
     *
     * @throws \LogicException
     * @throws \InvalidArgumentException when the path is invalid
     */
    public function getMenu($menu, array $pathName = array(), array $options = array())
    {
        if (!$menu instanceof ItemInterface) {
            $menu = $this->provider->get((string) $menu, array_merge($options, array('check_access' => false)));
        }
        foreach ($pathName as $child) {
            $menu = $menu->getChild($child);
            if ($menu === null) {
                throw new \InvalidArgumentException(sprintf('The menu has no child named "%s"', $child));
            }
        }

        return $menu;
    }

    /**
     * Find current menu item
     *
     * @param $menu
     * @return null|ItemInterface
     */
    public function getCurrentMenuItem($menu)
    {
        foreach ($menu as $item) {
            if ($this->matcher->isCurrent($item)) {
                return $item;
            }

            if ($item->getChildren() && $currentChild = $this->getCurrentMenuItem($item)) {
                return $currentChild;
            }
        }

        return null;
    }

    /**
     * Find menu item by route
     *
     * @param $menu
     * @param $route
     * @return ItemInterface
     */
    public function getMenuItemByRoute($menu, $route)
    {
        foreach ($menu as $item) {
            /** @var $item ItemInterface */

            $routes = (array)$item->getExtra('routes', array());
            if ($this->match($routes, $route)) {
                return $item;
            }

            if ($item->getChildren() && $currentChild = $this->getMenuItemByRoute($item, $route)) {
                return $currentChild;
            }
        }
    }



    /**
     * Return breadcrumb array
     *
     * @param $menuName
     * @param $item
     * @param bool $isInverse
     * @return array
     */
    public function getBreadcrumbArray($menuName, $item, $isInverse = true)
    {
        $manipulator = new MenuManipulator();
        $breadcrumbs = $manipulator->getBreadcrumbsArray($item);
        if ($breadcrumbs[0]['label'] == $menuName) {
            unset($breadcrumbs[0]);
        }

        if (!$isInverse) {
            $breadcrumbs = array_reverse($breadcrumbs);
        }

        return $breadcrumbs;
    }

    /**
     * Get menu item breadcrumbs list
     *
     * @param $menu
     * @param $route
     * @return array
     */
    public function getBreadcrumbLabels($menu, $route)
    {
        $labels = array();
        $menuItem = $this->getMenuItemByRoute($this->getMenu($menu), $route);
        if ($menuItem) {
            $breadcrumb = $this->getBreadcrumbArray($menu, $menuItem, false);
            foreach ($breadcrumb as $breadcrumbItem) {
                $labels[] = $breadcrumbItem['label'];
            }
        }

        return $labels;
    }

    /**
     * Match routes
     *
     * @param array $routes
     * @param $route
     * @return bool
     */
    protected function match(array $routes, $route)
    {
        foreach ($routes as $testedRoute) {
            if (!$this->routeMatch($testedRoute, $route)) {
                continue;
            }

            return true;
        }

        return false;
    }

    /**
     * Match routes
     *
     * @param string $pattern
     * @param string $route
     * @return boolean
     */
    protected function routeMatch($pattern, $route)
    {
        if ($pattern == $route) {

            return true;
        } elseif (0 === strpos($pattern, '/') && strlen($pattern) - 1 === strrpos($pattern, '/')) {

            return preg_match($pattern, $route);
        } elseif (false !== strpos($pattern, '*')) {
            $pattern = sprintf('/^%s$/', str_replace('*', '\w+', $pattern));

            return preg_match($pattern, $route);
        } else {

            return false;
        }
    }
}
