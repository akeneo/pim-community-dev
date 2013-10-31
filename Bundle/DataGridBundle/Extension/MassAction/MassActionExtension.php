<?php

namespace Oro\Bundle\DataGridBundle\Extension\MassAction;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Extension\Action\ActionExtension;

class MassActionExtension extends ActionExtension
{
    const MASS_ACTION_KEY     = 'mass_actions';
    const METADATA_ACTION_KEY = 'massActions';

    /** @var array */
    protected $actions = [];

    /**
     * {@inheritDoc}
     */
    public function isApplicable(DatagridConfiguration $config)
    {
        $massActions = $config->offsetGetOr(self::MASS_ACTION_KEY, []);

        return !empty($massActions);
    }

    public function getMassAction($name, DatagridConfiguration $config)
    {

    }
}
