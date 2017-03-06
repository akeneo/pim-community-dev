<?php

namespace Oro\Bundle\NavigationBundle\Entity\Builder;

class ItemFactory
{
    /**
     * Collection of builders grouped by alias
     *
     * @var array
     */
    protected $builders = [];

    /**
     * Add builder
     *
     * @param AbstractBuilder $builder
     */
    public function addBuilder(AbstractBuilder $builder)
    {
        $this->builders[$builder->getType()] = $builder;
    }

    /**
     * Create navigation item
     *
     * @param  string      $type
     * @param  array       $params
     * @return null|object
     */
    public function createItem($type, $params)
    {
        if (!array_key_exists($type, $this->builders)) {
            return null;
        }

        /** @var $builder AbstractBuilder */
        $builder = $this->builders[$type];

        return $builder->buildItem($params);
    }

    /**
     * Get navigation item
     *
     * @param  string      $type
     * @param  int         $itemId
     * @return null|object
     */
    public function findItem($type, $itemId)
    {
        if (!array_key_exists($type, $this->builders)) {
            return null;
        }

        /** @var $builder AbstractBuilder */
        $builder = $this->builders[$type];

        return $builder->findItem($itemId);
    }
}
