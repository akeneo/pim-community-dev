<?php
namespace Bap\Bundle\UIBundle\Menu;

use Knp\Menu\FactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Bap\Bundle\UIBundle\Events\ConfigureMenuEvent;

/**
 * Provide menu builder to build menu from bundles configuration
 * @see https://github.com/liuggio/KnpMenuExtensionBundle
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class Builder
{

    private $factory;

    private $eventDispatcher;

    /**
     * @param FactoryInterface $factory
     * @param EventDispatcherInterface $event_dispatcher
     */
    public function __construct(FactoryInterface $factory, EventDispatcherInterface $eventDispatcher)
    {
        $this->factory = $factory;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * This create a menu and dispatch an event
     *
     * @param Request $request
     * @param string $eventName
     * @param string $menuName
     * @return \Knp\Menu\ItemInterface
     */
    public function createMenu(Request $request, $eventName = 'Menu_event', $menuName = null )
    {
        $cssId = $menuName;

        if ($menuName == null) {
            $menuName = $eventName;
        }
        $menu = $this->factory->createItem($menuName);

        $menu->setChildrenAttribute('id', $cssId);

        //$menu->setCurrentUri($request->getRequestUri());
        $this->eventDispatcher->dispatch($eventName, new ConfigureMenuEvent($this->factory, $menu));
        return $menu;
    }
}