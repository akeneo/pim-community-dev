<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\DataGridBundle\EventListener;

use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Pim\Bundle\DataGridBundle\Datagrid\Configuration\Product\ColumnsConfigurator;
use Pim\Bundle\DataGridBundle\Datagrid\Configuration\Product\ContextConfigurator;
use Pim\Bundle\DataGridBundle\Datagrid\Configuration\Product\FiltersConfigurator;
use Pim\Bundle\DataGridBundle\Datagrid\Configuration\Product\SortersConfigurator;
use Pim\Bundle\DataGridBundle\EventListener\ConfigureProductGridListener as BaseConfigureProductGridListener;
use PimEnterprise\Bundle\DataGridBundle\Datagrid\Configuration\Product\RowActionsConfigurator;

/**
 * Grid listener to configure columns, filters, sorters and rows actions
 * based on product attributes, business rules and permissions
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
class ConfigureProductGridListener extends BaseConfigureProductGridListener
{
    /** @var RowActionsConfigurator */
    protected $actionsConfigurator;

    /**
     * @param ContextConfigurator    $contextConfigurator
     * @param ColumnsConfigurator    $columnsConfigurator
     * @param FiltersConfigurator    $filtersConfigurator
     * @param SortersConfigurator    $sortersConfigurator
     * @param RowActionsConfigurator $actionsConfigurator
     */
    public function __construct(
        ContextConfigurator $contextConfigurator,
        ColumnsConfigurator $columnsConfigurator,
        FiltersConfigurator $filtersConfigurator,
        SortersConfigurator $sortersConfigurator,
        RowActionsConfigurator $actionsConfigurator = null
    ) {
        parent::__construct($contextConfigurator, $columnsConfigurator, $filtersConfigurator, $sortersConfigurator);
        $this->actionsConfigurator = $actionsConfigurator;
    }

    /**
     * {@inheritdoc}
     */
    public function buildBefore(BuildBefore $event)
    {
        parent::buildBefore($event);

        $datagridConfig = $event->getConfig();
        if ($this->getRowActionsConfigurator()) {
            $this->getRowActionsConfigurator()->configure($datagridConfig);
        }
    }

    /**
     * @return RowActionsConfigurator
     */
    protected function getRowActionsConfigurator()
    {
        return $this->actionsConfigurator;
    }
}
