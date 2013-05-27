<?php

namespace Oro\Bundle\NavigationBundle\Entity\Builder;

use Oro\Bundle\NavigationBundle\Entity\Builder\AbstractBuilder;
use Oro\Bundle\NavigationBundle\Entity\PinbarTab;
use Oro\Bundle\NavigationBundle\Entity\NavigationItem;

class PinbarTabBuilder extends AbstractBuilder
{
    /**
     * Build navigation item
     *
     * @param $params
     * @return object|null
     */
    public function buildItem($params)
    {
        $navigationItem = new NavigationItem($params);
        $navigationItem->setType($this->getType());

        $pinbarTabItem = new PinbarTab();
        $pinbarTabItem->setItem($navigationItem);
        $pinbarTabItem->setMaximized(!empty($params['maximized']));

        return $pinbarTabItem;
    }

    /**
     * Find navigation item
     *
     * @param  int            $itemId
     * @return PinbarTab|null
     */
    public function findItem($itemId)
    {
        return $this->getEntityManager()->find('OroNavigationBundle:PinbarTab', $itemId);
    }
}
