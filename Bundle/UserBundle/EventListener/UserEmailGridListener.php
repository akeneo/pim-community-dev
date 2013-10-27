<?php

namespace Oro\Bundle\UserBundle\EventListener;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityManager;

use Symfony\Component\DependencyInjection\ContainerInterface;

use Oro\Bundle\EmailBundle\Datagrid\EmailQueryFactory;
use Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource;
use Oro\Bundle\DataGridBundle\Event\BuildAfter;

class UserEmailGridListener
{
    /** @var  EmailQueryFactory */
    protected $queryFactory;

    /** @var \Symfony\Component\HttpFoundation\Request  */
    protected $request;

    /** @var  EntityManager */
    protected $em;

    public function __construct(ContainerInterface $container, EmailQueryFactory $factory)
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
            $queryBuilder = $datasource->getQuery();

            $this->queryFactory->prepareQuery($queryBuilder);

            if ($id = $this->request->get('userId')) {
                $user = $this->em
                    ->getRepository('OroUserBundle:User')
                    ->find($id);

                // TODO: select imap configuration by userId
                $origin = $user->getImapConfiguration();
                $origin = $origin !== null ? $origin->getId() : null;
            } else {
                $origin = null;
            }

            $queryBuilder->setParameter('origin_id', $origin);
        }
    }
}
