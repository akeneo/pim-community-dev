<?php

namespace Oro\Bundle\NavigationBundle\Entity\Builder;

use Oro\Bundle\NavigationBundle\Entity\NavigationHistoryItem;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

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
        if (isset($params['url'])) {
            $params['_route'] = $this->extractRouteParameters($params['url']);
        }
        return new NavigationHistoryItem($params);
    }

    /**
     * Find navigation item
     *
     * @param  int $itemId
     *
     * @return NavigationHistoryItem|null
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    public function findItem($itemId)
    {
        return $this->getEntityManager()->find('OroNavigationBundle:NavigationHistoryItem', $itemId);
    }

    private function extractRouteParameters($url)
    {
        try {
            return $this->getRouter()->match($url);
        } catch (ResourceNotFoundException $e) {
            return null;
        }
    }
}
