<?php

namespace Oro\Bundle\NavigationBundle\Entity\Builder;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Routing\RouterInterface;

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

    /** @var RouterInterface */
    private $router;

    /**
     * @param EntityManager   $em
     * @param RouterInterface $router
     * @param string          $type
     */
    public function __construct(EntityManager $em, RouterInterface $router, $type)
    {
        $this->em = $em;
        $this->type = $type;
        $this->router = $router;
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

    /**
     * @return RouterInterface
     */
    protected function getRouter()
    {
        return $this->router;
    }
}
