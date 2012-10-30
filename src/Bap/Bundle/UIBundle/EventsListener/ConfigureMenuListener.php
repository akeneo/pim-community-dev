<?php
namespace Bap\Bundle\UIBundle\EventsListener;

use Knp\Menu\MenuItem;
use Bap\Bundle\UIBundle\Events\ConfigureMenuEvent;

class ConfigureMenuListener
{
    const DEFAULT_ROOT_NAME = "root";

    /**
     * will contain the structure for the menu
     * @var array
     */
    private $menuChildArray;

    public function __construct($childArray)
    {
       $this->menuChildArray = $childArray;
    }

    /**
     * function called by the event manager
     * @param Liuggio\KnpMenuExtensionBundle\Events\ConfigureMenuEvent $event
     */
    public function onMenuConfigure(ConfigureMenuEvent $event)
    {
        $menu = $event->getMenu();
        if (!$menu) {
            throw new \InvalidArgumentException(sprintf('Invalid event created during the menu creation'));
        }
        // if there's no parent take the menu root
        foreach ($this->menuChildArray as $father => $child) {
            $menu = $this->getParent($menu, $father);
            $menu = $this->addChildren($menu, $child);
        }
    }

    /**
     * Returns the child menu identified by the given name
     *
     * @param \Knp\Menu\ItemInterface $menu Then menu
     * @param string $parentName Then name of the child menu to return
     * @return \Knp\Menu\ItemInterface|null
     * @throw \Exception
     */
    private function getParent(\Knp\Menu\ItemInterface $menu, $parentName = null)
    {
        if (strcmp($parentName, self::DEFAULT_ROOT_NAME) == 0) {
            return $menu;
        } else {
            $menuParent = $menu->getChild($parentName);
            if ($menuParent == null) {
                throw new \InvalidArgumentException(sprintf('Invalid parent name passed to getParent, there is no menu-child with the name given \'%s\', check your config', $parentName));
            } else {
                return $menuParent;
            }
        }
    }

    /**
     * it creates all the children
     *
     * @param \Knp\Menu\ItemInterface $menu
     * @param array $childrenArray
     * @return \Knp\Menu\ItemInterface
     */
    private function addChildren($menu, $childrenArray)
    {
        foreach ($childrenArray as $name => $option) {
           $menu->addChild($name, $option);
        }
        return $menu;
    }
}