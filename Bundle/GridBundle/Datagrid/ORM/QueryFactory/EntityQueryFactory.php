<?php

namespace Oro\Bundle\GridBundle\Datagrid\ORM\QueryFactory;

use Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Doctrine\ORM\QueryBuilder;

class EntityQueryFactory extends AbstractQueryFactory
{
    /**
     * @var RegistryInterface
     */
    protected $registry;

    /**
     * @var string
     */
    protected $className;

    /**
     * @var string
     */
    protected $alias;

    /**
     * @param RegistryInterface $registry
     * @param string $className
     * @param string $alias
     */
    public function __construct(RegistryInterface $registry, $className, $alias = 'o')
    {
        $this->registry  = $registry;
        $this->className = $className;
        $this->alias     = $alias;
    }

    /**
     * @return ProxyQueryInterface
     */
    public function createQuery()
    {
        $entityManager = $this->registry->getManagerForClass($this->className);
        $this->queryBuilder = $entityManager->getRepository($this->className)->createQueryBuilder($this->alias);

        return parent::createQuery();
    }

    /**
     * @return string
     */
    public function getClassName()
    {
        return $this->className;
    }
}
