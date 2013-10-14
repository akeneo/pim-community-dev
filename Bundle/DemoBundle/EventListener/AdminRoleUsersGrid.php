<?php

namespace Oro\Bundle\DemoBundle\EventListener;

use Oro\Bundle\DataGridBundle\Datasource\OrmDatasource;
use Oro\Bundle\DataGridBundle\Event\BuildAfter;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

class AdminRoleUsersGrid
{
    protected $request;
    protected $em;

    public function __construct(ContainerInterface $container)
    {
        $this->request = $container->get('request');
        $this->em      = $container->get('doctrine.orm.entity_manager');
    }

    public function onBuildAfter(BuildAfter $event)
    {
        $datasource = $event->getDatagrid()->getDatasource();

        if ($datasource instanceof OrmDatasource) {
            $aliases = $datasource->getQuery()->getRootAliases();
            $entityAlias = reset($aliases);

            if ($id = $this->request->get('roleId')) {
                $role = $this->em->getRepository('OroUserBundle:Role')->find($id);

                $hasRoleExpression =
                    "CASE WHEN " .
                    "((:role MEMBER OF $entityAlias.roles) OR $entityAlias.id IN (:data_in)) AND " .
                    "$entityAlias.id NOT IN (:data_not_in)" .
                    "THEN true ELSE false END";
                $datasource->getQuery()->setParameter(':role', $role);
            } else {
                $hasRoleExpression =
                    "CASE WHEN " .
                    "$entityAlias.id IN (:data_in) AND $entityAlias.id NOT IN (:data_not_in) " .
                    "THEN true ELSE false END";
            }

            $datasource->getQuery()->addSelect($hasRoleExpression . ' as hasRole');
            $datasource->getQuery()->setParameter(':data_in', $this->request->get('data_in', array(0)));
            $datasource->getQuery()->setParameter(':data_not_in', $this->request->get('data_not_in', array(0)));
        }
    }
}
