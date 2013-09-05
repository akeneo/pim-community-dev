<?php
namespace Pim\Bundle\CatalogBundle\Datagrid;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\QueryBuilder;

/**
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DatagridWorker implements DatagridWorkerInterface
{
    private $container;
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }
    
    /**
     * @inheritdoc
     */
    public function getDataAuditDatagrid($entity, $route, array $routeParams)
    {
        if (!is_object($entity)) {
            throw new \InvalidArgumentException(
                sprintf('Expected Object argument, got %s', gettype($entity))
            );
        }
        $queryFactory = $this->getQueryFactory('history');
        $doctrine = $this->container->get('doctrine');
        $queryFactory->setQueryBuilder(
            $doctrine->getRepository('OroDataAuditBundle:Audit')->getLogEntriesQueryBuilder($entity)
        );
        $datagridManager = $this->getDatagridManager('history');
        $datagridManager->getRouteGenerator()->setRouteName($route);
        $datagridManager->getRouteGenerator()->setRouteParameters($routeParams);

        return $datagridManager->getDatagrid();
    }
    /**
     * @inheritdoc
     */
    public function getDatagrid($name, QueryBuilder $queryBuilder = null, $namespace = 'pim_catalog')
    {
        if ($queryBuilder) {
            $queryFactory = $this->getQueryFactory($name, $namespace);
            $queryFactory->setQueryBuilder($queryBuilder);
        }
        return $this->getDatagridManager($name, $namespace)->getDatagrid();
    }
    
    /**
     * @inheritdoc
     */
    public function getDatagridManager($name, $namespace = 'pim_catalog')
    {
        return $this->container->get(sprintf('%s.datagrid.manager.%s', $namespace, $name));
    }
    
    private function getQueryFactory($name, $namespace = 'pim_catalog')
    {
        return $this->container->get(sprintf('%s.datagrid.manager.%s.default_query_factory', $namespace, $name));
    }
}
