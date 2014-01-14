<?php

namespace Pim\Bundle\DataGridBundle\Extension\ExportAction;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\DataGridBundle\Extension\Action\ActionExtension;
use Oro\Bundle\DataGridBundle\Extension\Action\Actions\ActionInterface;

/**
 * Export action extension
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ExportActionExtension extends ActionExtension
{
    const ACTION_KEY          = 'export_actions';
    const METADATA_ACTION_KEY = 'exportActions';

    /** @var array */
    protected $actions = [];

    /**
     * {@inheritDoc}
     */
    public function isApplicable(DatagridConfiguration $config)
    {
        $exportActions = $config->offsetGetOr(static::ACTION_KEY, []);

        return !empty($exportActions);
    }

    /**
     * Get grid export action by name
     *
     * @param string            $name
     * @param DatagridInterface $datagrid
     *
     * @return bool|ActionInterface
     */
    public function getExportAction($name, DatagridInterface $datagrid)
    {
        $config = $datagrid->getAcceptor()->getConfig();

        $action = false;
        if (isset($config[static::ACTION_KEY][$name])) {
            $action = $this->getActionObject($name, $config[static::ACTION_KEY][$name]);
        }

        return $action;
    }
}
