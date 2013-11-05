<?php

namespace Oro\Bundle\DemoBundle\EventListener;

use Symfony\Component\DependencyInjection\ContainerInterface;

use Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource;
use Oro\Bundle\DataGridBundle\Event\BuildAfter;

class AdminRoleUsersGrid
{
    /**
     * @var \Symfony\Component\HttpFoundation\Request
     */
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
            $qb = $datasource->getQueryBuilder();

            $aliases = $qb->getRootAliases();
            $entityAlias = reset($aliases);

            if ($id = $this->request->get('roleId')) {
                $role = $this->em->getRepository('OroUserBundle:Role')->find($id);

                $hasRoleExpression =
                    "CASE WHEN " .
                    "((:role MEMBER OF $entityAlias.roles) OR $entityAlias.id IN (:data_in)) AND " .
                    "$entityAlias.id NOT IN (:data_not_in)" .
                    "THEN true ELSE false END";
                $qb->setParameter(':role', $role);
            } else {
                $hasRoleExpression =
                    "CASE WHEN " .
                    "$entityAlias.id IN (:data_in) AND $entityAlias.id NOT IN (:data_not_in) " .
                    "THEN true ELSE false END";
            }

            $qb->addSelect($hasRoleExpression . ' as hasRole')
               ->setParameter(':data_in', $this->request->get('data_in', array(0)))
               ->setParameter(':data_not_in', $this->request->get('data_not_in', array(0)));
        }
    }
}
