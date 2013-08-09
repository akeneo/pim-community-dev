<?php

namespace Oro\Bundle\GridBundle\Datagrid\ORM\QueryFactory;

use Symfony\Bridge\Doctrine\RegistryInterface;

use Doctrine\ORM\EntityRepository;

use Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface;
use Oro\Bundle\GridBundle\Datagrid\ORM\EntityProxyQuery;

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
        /** @var EntityRepository $repository */
        $repository = $entityManager->getRepository($this->className);
        $this->queryBuilder = $repository->createQueryBuilder($this->alias);

        return new EntityProxyQuery($this->queryBuilder);
    }

    /**
     * @return string
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * @return string
     */
    public function getAlias()
    {
        return $this->alias;
    }
}
