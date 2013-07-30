<?php

namespace Oro\Bundle\NavigationBundle\Menu;

use Knp\Menu\ItemInterface;
use Knp\Menu\Matcher\Matcher;
use Knp\Menu\Util\MenuManipulator;

class NavigationHistoryBuilder extends NavigationItemBuilder
{
    /**
     * @var Matcher
     */
    private $matcher;

    /**
     * @var \Oro\Bundle\ConfigBundle\Config\UserConfigManager
     */
    private $configOptions = null;

    /**
     * @var MenuManipulator
     */
    private $manipulator;

    /**
     * @return MenuManipulator
     */
    protected function getMenuManipulator()
    {
        if (!$this->manipulator) {
            $this->manipulator = new MenuManipulator();
        }
        return $this->manipulator;
    }

    /**
     * Inject config
     *
     * @param \Oro\Bundle\ConfigBundle\Config\UserConfigManager $config
     */
    public function setOptions(\Oro\Bundle\ConfigBundle\Config\UserConfigManager $config)
    {
        $this->configOptions = $config;
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
        $maxItems = $this->configOptions->get('oro_navigation.maxItems');

        if (!is_null($maxItems)) {
            // we'll hide current item, so always select +1 item
            $options['maxItems'] = $maxItems + 1;
        }

        parent::build($menu, $options, $alias);

        $children = $menu->getChildren();
        foreach ($children as $child) {
            if ($this->matcher->isCurrent($child)) {
                $menu->removeChild($child);

                break;
            }
        }

        $this->getMenuManipulator()->slice($menu, 0, $maxItems);
    }

    /**
     * Setter for matcher service
     *
     * @param \Knp\Menu\Matcher\Matcher $matcher
     * @return $this
     */
    public function setMatcher(Matcher $matcher)
    {
        $this->matcher = $matcher;

        return $this;
    }
}
