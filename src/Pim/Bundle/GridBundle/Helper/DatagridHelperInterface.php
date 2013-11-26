<?php
namespace Pim\Bundle\GridBundle\Helper;

use Oro\Bundle\GridBundle\Datagrid\Datagrid;
use Oro\Bundle\GridBundle\Datagrid\DatagridManagerInterface;
use Doctrine\ORM\QueryBuilder;

/**
 * Shortcut methods used to manage datagrids
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface DatagridHelperInterface
{
    /**
     * Get the log entries datagrid for the given product
     *
     * @param mixed  $entity
     * @param string $route
     * @param array  $routeParams
     *
     * @return Datagrid
     *
     * @throws \InvalidArgumentException
     */
    public function getDataAuditDatagrid($entity, $route, array $routeParams);

    /**
     * Gets the datagrid for the given type and querybuilder
     *
     * @param string       $name
     * @param QueryBuilder $queryBuilder
     * @param string       $namespace
     *
     * @return Datagrid
     */
    public function getDatagrid($name, QueryBuilder $queryBuilder = null, $namespace = 'pim_catalog');

    /**
     * Gets the datagrid manager for the given type
     *
     * @param string $name
     * @param string $namespace
     *
     * @return DatagridManagerInterface
     */
    public function getDatagridManager($name, $namespace = 'pim_catalog');

    /**
     * @return Oro\Bundle\GridBundle\Renderer\GridRenderer
     */
    public function getDatagridRenderer();
}
