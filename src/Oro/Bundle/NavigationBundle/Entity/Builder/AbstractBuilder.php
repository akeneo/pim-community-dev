<?php

namespace Oro\Bundle\NavigationBundle\Entity\Builder;

use Doctrine\ORM\EntityManager;

abstract class AbstractBuilder
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var string
     */
    private $type;

    /**
     * @param EntityManager $em
     * @param string        $type
     */
    public function __construct(EntityManager $em, $type)
    {
        $this->em = $em;
        $this->type = $type;
    }

    /**
     * Build navigation item
     *
     * @param $params
     * @return object|null
     */
    abstract public function buildItem($params);

    /**
     * Find navigation item
     *
     * @param $params
     * @return object|null
     */
    abstract public function findItem($params);

    /**
     *
     * @return EntityManager
     */
    public function getEntityManager()
    {
        return $this->em;
    }

    /**
     * Get entity type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }
}
