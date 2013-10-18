<?php

namespace Oro\Bundle\UserBundle\EventListener;

use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\UserBundle\Datagrid\UserEmailQueryFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Oro\Bundle\DataGridBundle\Datasource\OrmDatasource;
use Oro\Bundle\DataGridBundle\Event\BuildAfter;

class UserEmailGridListener
{
    protected $request;
    protected $em;

    /** @var  UserEmailQueryFactory */
    protected $queryFactory;

    public function __construct(ContainerInterface $container, UserEmailQueryFactory $factory)
    {
        $this->request = $container->get('request');
        $this->em      = $container->get('doctrine.orm.entity_manager');
        $this->queryFactory = $factory;
    }

    public function onBuildAfter(BuildAfter $event)
    {
        $datasource = $event->getDatagrid()->getDatasource();

        if ($datasource instanceof OrmDatasource) {
            /** @var QueryBuilder $query */
            $query = $datasource->getQuery();

            /** @var QueryBuilder $query */
            $queryBuilder = $this->queryFactory
                ->createQuery()
                ->getQueryBuilder();
            $datasource->setQuery($queryBuilder);
        }
    }
}
