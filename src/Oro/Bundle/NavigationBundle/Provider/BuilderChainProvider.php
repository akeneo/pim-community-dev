<?php

namespace Oro\Bundle\NavigationBundle\Provider;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Knp\Menu\Provider\MenuProviderInterface;

use Oro\Bundle\NavigationBundle\Event\ConfigureMenuEvent;
use Oro\Bundle\NavigationBundle\Menu\BuilderInterface;

use \Doctrine\Common\Cache\CacheProvider;

class BuilderChainProvider implements MenuProviderInterface
{
    const COMMON_BUILDER_ALIAS = '_common_builder';

    /**
     * Collection of builders grouped by alias.
     *
     * @var array
     */
    protected $builders = array();

    /**
     * Collection of menus.
     *
     * @var array
     */
    protected $menus = array();

    /**
     * @var FactoryInterface
     */
    private $factory;

    /**
     * @var \Doctrine\Common\Cache\CacheProvider
     */
    private $cache;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    public function __construct(FactoryInterface $factory, EventDispatcherInterface $eventDispatcher)
    {
        $this->factory = $factory;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Set cache instance
     *
     * @param \Doctrine\Common\Cache\CacheProvider $cache
     */
    public function setCache(CacheProvider $cache)
    {
        $this->cache = $cache;
        $this->cache->setNamespace('oro_menu_instance');
    }

    /**
     * Add builder to chain.
     *
     * @param BuilderInterface $builder
     * @param string           $alias
     */
    public function addBuilder(BuilderInterface $builder, $alias = self::COMMON_BUILDER_ALIAS)
    {
        $this->assertAlias($alias);

        if (!array_key_exists($alias, $this->builders)) {
            $this->builders[$alias] = array();
        }
        $this->builders[$alias][] = $builder;
    }

    /**
     * Build menu.
     *
     * @param  string        $alias
     * @param  array         $options
     * @return ItemInterface
     */
    public function get($alias, array $options = array())
    {
        $this->assertAlias($alias);

        if (!array_key_exists($alias, $this->menus)) {
            if ($this->cache && $this->cache->contains($alias)) {
                $menuData = $this->cache->fetch($alias);
                $this->menus[$alias] = $this->factory->createFromArray($menuData);
            } else {
                $menu = $this->factory->createItem($alias);

                /** @var BuilderInterface $builder */
                // try to find builder for the specified menu alias
                if (array_key_exists($alias, $this->builders)) {
                    foreach ($this->builders[$alias] as $builder) {
                        $builder->build($menu, $options, $alias);
                    }
                }

                // In any case we must run common builder
                if (array_key_exists(self::COMMON_BUILDER_ALIAS, $this->builders)) {
                    foreach ($this->builders[self::COMMON_BUILDER_ALIAS] as $builder) {
                        $builder->build($menu, $options, $alias);
                    }
                }

                $this->menus[$alias] = $menu;

                $this->eventDispatcher->dispatch(
                    ConfigureMenuEvent::getEventName($alias),
                    new ConfigureMenuEvent($this->factory, $menu)
                );

                $this->sort($menu);
                if ($this->cache) {
                    $this->cache->save($alias, $menu->toArray());
                }
            }
        }

        return $this->menus[$alias];
    }

    /**
     * Reorder menu based on position attribute
     *
     * @param ItemInterface $menu
     */
    protected function sort(ItemInterface $menu)
    {
        if ($menu->hasChildren() && $menu->getDisplayChildren()) {
            $orderedChildren = array();
            $unorderedChildren = array();
            $hasOrdering = false;
            $children = $menu->getChildren();
            foreach ($children as &$child) {
                if ($child->hasChildren() && $child->getDisplayChildren()) {
                    $this->sort($child);
                }
                $position = $child->getExtra('position');
                if ($position !== null) {
                    $orderedChildren[$child->getName()] = (int) $position;
                    $hasOrdering = true;
                } else {
                    $unorderedChildren[] = $child->getName();
                }
            }
            if ($hasOrdering) {
                asort($orderedChildren);
                $menu->reorderChildren(array_merge(array_keys($orderedChildren), $unorderedChildren));
            }
        }
    }

    /**
     * Checks whether a menu exists in this provider
     *
     * @param  string  $alias
     * @param  array   $options
     * @return boolean
     */
    public function has($alias, array $options = array())
    {
        $this->assertAlias($alias);

        return array_key_exists($alias, $this->builders);
    }

    /**
     * Assert alias not empty
     *
     * @param  string                    $alias
     * @throws \InvalidArgumentException
     */
    protected function assertAlias($alias)
    {
        if (empty($alias)) {
            throw new \InvalidArgumentException('Menu alias was not set.');
        }
    }
}
