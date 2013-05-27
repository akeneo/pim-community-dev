<?php

namespace Oro\Bundle\NavigationBundle\Menu;

use Knp\Menu\ItemInterface;
use Oro\Bundle\NavigationBundle\Entity\NavigationHistoryItem;

class NavigationMostviewedBuilder extends NavigationItemBuilder
{
    /**
     * @var \Oro\Bundle\ConfigBundle\Config\UserConfigManager
     */
    private $configOptions = null;

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
        $options['orderBy'] = array(array('field' => NavigationHistoryItem::NAVIGATION_HISTORY_COLUMN_VISIT_COUNT));
        $maxItems = $this->configOptions->get('oro_navigation.maxItems');
        if (!is_null($maxItems)) {
            $options['maxItems'] = $maxItems;
        }
        parent::build($menu, $options, $alias);
    }
}
