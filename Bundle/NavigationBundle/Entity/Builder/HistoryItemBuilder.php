<?php

namespace Oro\Bundle\NavigationBundle\Entity\Builder;

use Oro\Bundle\NavigationBundle\Entity\Builder\AbstractBuilder;
use Oro\Bundle\NavigationBundle\Entity\NavigationHistoryItem;

class HistoryItemBuilder extends AbstractBuilder
{
    /**
     * Build navigation item
     *
     * @param $params
     * @return NavigationHistoryItem|null
     */
    public function buildItem($params)
    {
        return new NavigationHistoryItem($params);
    }

    /**
     * Find navigation item
     *
     * @param  int                        $itemId
     * @return NavigationHistoryItem|null
     */
    public function findItem($itemId)
    {
        return $this->getEntityManager()->find('OroNavigationBundle:NavigationHistoryItem', $itemId);
    }
}
