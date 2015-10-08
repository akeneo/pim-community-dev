<?php

namespace Oro\Bundle\NavigationBundle\Menu;

use Knp\Menu\ItemInterface;
use Oro\Bundle\NavigationBundle\Menu\BuilderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ConfigurationBuilder implements BuilderInterface
{
    /**
     * @var ContainerInterface $container
     */
    protected $container;

    /**
     * Inject service container
     *
     * @param ContainerInterface $container
     *
     * @return ConfigurationBuilder
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;

        return $this;
    }

    /**
     * Modify menu by adding, removing or editing items.
     *
     * @param \Knp\Menu\ItemInterface $menu
     * @param array                   $options
     * @param string|null             $alias
     */
    public function build(ItemInterface $menu, array $options = array(), $alias = null)
    {
        $menuConfig = $this->container->getParameter('oro_menu_config');

        if (!empty($menuConfig['items']) && !empty($menuConfig['tree'])) {
            foreach ($menuConfig['tree'] as $menuTreeName => $menuTreeElement) {
                if ($menuTreeName == $alias) {

                    if (!empty($menuTreeElement['extras'])) {
                        $menu->setExtras($menuTreeElement['extras']);
                    }

                    if (!empty($menuTreeElement['type'])) {
                        $menu->setExtra('type', $menuTreeElement['type']);
                    }

                    $this->createFromArray($menu, $menuTreeElement['children'], $menuConfig['items'], $options);
                }
            }
        }
    }

    /**
     * @param ItemInterface $menu
     * @param array         $data
     * @param array         $itemList
     * @param array         $options
     *
     * @return \Knp\Menu\ItemInterface
     */
    private function createFromArray(ItemInterface $menu, array $data, array &$itemList, array $options = array())
    {
        $isAllowed = false;
        foreach ($data as $itemCode => $itemData) {
            if (!empty($itemList[$itemCode])) {

                $itemOptions = $itemList[$itemCode];

                if (empty($itemOptions['name'])) {
                    $itemOptions['name'] = $itemCode;
                }

                if (empty($itemOptions['route']) && empty($itemOptions['uri'])) {
                    $itemOptions['route'] = $itemCode;
                }

                if (!empty($itemData['position'])) {
                    $itemOptions['extras']['position'] = $itemData['position'];
                }
                $this->moveToExtras($itemOptions, 'translateDomain');
                $this->moveToExtras($itemOptions, 'translateParameters');

                $newMenuItem = $menu->addChild($itemOptions['name'], array_merge($itemOptions, $options));

                if (!empty($itemData['children'])) {
                    $this->createFromArray($newMenuItem, $itemData['children'], $itemList, $options);
                }

                $isAllowed = $isAllowed || $newMenuItem->getExtra('isAllowed');
            }
        }
        $menu->setExtra('isAllowed', $isAllowed);

        if ($menu->getExtra('hideIfEmpty') && $menu->hasChildren()) {
            $willDisplaySomeChildren = false;

            foreach ($menu->getChildren() as $child) {
                if ($child->isDisplayed() && $child->getExtra('isAllowed')) {
                    $willDisplaySomeChildren = true;
                    break;
                }
            }

            if (!$willDisplaySomeChildren) {
                $menu->setDisplay(false);
            }
        }
    }

    /**
     * @param array  $menuItem
     * @param string $optionName
     *
     * @return void
     */
    private function moveToExtras(array &$menuItem, $optionName)
    {
        if (isset($menuItem[$optionName])) {
            $menuItem['extras'][$optionName] = $menuItem[$optionName];
            unset($menuItem[$optionName]);
        }
    }
}
