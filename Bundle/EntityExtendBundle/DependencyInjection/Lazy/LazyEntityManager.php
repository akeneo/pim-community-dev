<?php

namespace Oro\Bundle\EntityExtendBundle\DependencyInjection\Lazy;

use Doctrine\ORM\EntityManager;

use Symfony\Component\DependencyInjection\ContainerInterface;

class LazyEntityManager
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var EntityManager
     */
    protected $em;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return EntityManager
     */
    public function getEntityManager()
    {
        $this->init();

        return $this->em;
    }

    /**
     *
     */
    protected function init()
    {
        if ($this->em === null) {
            $this->em = $this->container->get('doctrine.orm.default_entity_manager');
        }
    }
}