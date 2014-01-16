<?php

namespace Pim\Bundle\GridBundle\Helper;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\QueryBuilder;

/**
 * Shortcut methods used to manage datagrids
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DatagridHelper implements DatagridHelperInterface
{
    /**
     * @var ContainerInterface $container
     */
    protected $container;

    /**
     * Constructor
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function getDatagridManager($name, $namespace = 'pim_catalog')
    {
        return $this->container->get(sprintf('%s.datagrid.manager.%s', $namespace, $name));
    }

    /**
     * {@inheritdoc}
     */
    public function getDatagridRenderer()
    {
        return $this->container->get('oro_grid.renderer');
    }

    /**
     * Gets the query factory for the given type
     *
     * @param string $name
     * @param string $namespace
     *
     * @return QueryFactoryInterface
     */
    protected function getQueryFactory($name, $namespace = 'pim_catalog')
    {
        return $this->container->get(sprintf('%s.datagrid.manager.%s.default_query_factory', $namespace, $name));
    }
}
