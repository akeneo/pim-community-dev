<?php

namespace Oro\Bundle\NavigationBundle\Entity\Builder;

use Oro\Bundle\NavigationBundle\Entity\Builder\AbstractBuilder;
use Oro\Bundle\NavigationBundle\Entity\NavigationItem;

class NavigationItemBuilder extends AbstractBuilder
{
    /**
     * Build navigation item
     *
     * @param $params
     * @return NavigationItem|null
     */
    public function buildItem($params)
    {
        $navigationItem = new NavigationItem($params);
        $navigationItem->setType($this->getType());

        return $navigationItem;
    }

    /**
     * Find navigation item
     *
     * @param  int                 $itemId
     * @return NavigationItem|null
     */
    public function findItem($itemId)
    {
        return $this->getEntityManager()->find('OroNavigationBundle:NavigationItem', $itemId);
    }
}
