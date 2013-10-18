<?php

namespace Oro\Bundle\UserBundle\EventListener;

use Doctrine\ORM\QueryBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Oro\Bundle\EmailBundle\Datagrid\EmailQueryFactory;
use Oro\Bundle\DataGridBundle\Datasource\OrmDatasource;
use Oro\Bundle\DataGridBundle\Event\BuildAfter;

class UserEmailGridListener
{
    /** @var  EmailQueryFactory */
    protected $queryFactory;

    public function __construct(EmailQueryFactory $factory)
    {
        $this->queryFactory = $factory;
    }

    public function onBuildAfter(BuildAfter $event)
    {
        $datasource = $event->getDatagrid()->getDatasource();
        if ($datasource instanceof OrmDatasource) {
            /** @var QueryBuilder $query */
            $queryBuilder = $datasource->getQuery();

            $this->queryFactory->prepareQuery($queryBuilder);

            // TODO: find user, current user you're viewing
            $user = 'something';
            $origin = $user->getImapConfiguration();
            $queryBuilder->setParameter('origin_id', $origin !== null ? $origin->getId() : null);
        }
    }
}
