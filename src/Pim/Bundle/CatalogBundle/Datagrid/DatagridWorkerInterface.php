<?php
namespace Pim\Bundle\CatalogBundle\Datagrid;

use Oro\Bundle\GridBundle\Datagrid\Datagrid;
use Oro\Bundle\GridBundle\Datagrid\DatagridManagerInterface;
use Doctrine\ORM\QueryBuilder;

/**
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface DatagridWorkerInterface
{
    /**
     * Get the log entries datagrid for the given product
     *
     * @param mixed  $entity
     * @param string $route
     * @param array  $routeParams
     *
     * @return Datagrid
     */
    public function getDataAuditDatagrid($entity, $route, array $routeParams);
    
    /**
     * Gets the datagrid for the given type and querybuilder
     * 
     * @param QueryBuilder $builder
     * @param type $name
     * @return Datagrid
     */
    public function getDatagrid($name, QueryBuilder $queryBuilder = null, $namespace = 'pim_catalog');
    
    /**
     * Gets the datagrid manager for the given type
     * 
     * @param QueryBuilder $builder
     * @param type $name
     * @return DatagridManagerInterface
     */
    public function getDatagridManager($name, $namespace = 'pim_catalog');
}
