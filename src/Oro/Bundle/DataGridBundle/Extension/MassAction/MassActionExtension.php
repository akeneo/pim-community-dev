<?php

namespace Oro\Bundle\DataGridBundle\Extension\MassAction;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\Common\MetadataIterableObject;
use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\DataGridBundle\Extension\Action\ActionExtension;
use Oro\Bundle\DataGridBundle\Extension\Action\Actions\ActionInterface;

class MassActionExtension extends ActionExtension
{
    const ACTION_KEY = 'mass_actions';
    const MASS_ACTIONS_GROUPS_KEY = 'mass_actions_groups';
    const METADATA_ACTION_KEY = 'massActions';
    const METADATA_MASS_ACTIONS_GROUPS_KEY = 'massActionsGroups';

    /** @var array */
    protected $actions = [];

    /**
     * {@inheritDoc}
     */
    public function isApplicable(DatagridConfiguration $config)
    {
        $massActions = $config->offsetGetOr(static::ACTION_KEY, []);

        return !empty($massActions);
    }

    /**
     * Get grid massaction by name
     *
     * @param string           $name
     * @param DatagridInterface $datagrid
     *
     * @return bool|ActionInterface
     */
    public function getMassAction($name, DatagridInterface $datagrid)
    {
        $config = $datagrid->getAcceptor()->getConfig();

        $action = false;
        if (isset($config[static::ACTION_KEY][$name])) {
            $action = $this->getActionObject($name, $config[static::ACTION_KEY][$name]);
        }

        return $action;
    }

    public function visitMetadata(DatagridConfiguration $config, MetadataIterableObject $data)
    {
        parent::visitMetadata($config, $data);

        $data->offsetAddToArray(
            static::METADATA_MASS_ACTIONS_GROUPS_KEY,
            $config->offsetGetOr(static::MASS_ACTIONS_GROUPS_KEY, [])
        );
    }
}
