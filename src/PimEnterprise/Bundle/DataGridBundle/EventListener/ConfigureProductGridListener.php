<?php

namespace PimEnterprise\Bundle\DataGridBundle\EventListener;

use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Pim\Bundle\DataGridBundle\Datagrid\Product\ContextConfigurator;
use Pim\Bundle\DataGridBundle\Datagrid\Product\ColumnsConfigurator;
use Pim\Bundle\DataGridBundle\Datagrid\Product\SortersConfigurator;
use Pim\Bundle\DataGridBundle\Datagrid\Product\FiltersConfigurator;
use PimEnterprise\Bundle\DataGridBundle\Datagrid\Product\RowActionsConfigurator;
use Pim\Bundle\DataGridBundle\EventListener\ConfigureProductGridListener as BaseConfigureProductGridListener;

/**
 * Grid listener to configure columns, filters, sorters and rows actions
 * based on product attributes, business rules and permissions
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class ConfigureProductGridListener extends BaseConfigureProductGridListener
{
    /**
     * @var RowActionsConfigurator
     */
    protected $rowActionsConfigurator;

    /**
     * Constructor
     *
     * @param ContextConfigurator    $contextConfigurator
     * @param ColumnsConfigurator    $columnsConfigurator
     * @param FiltersConfigurator    $filtersConfigurator
     * @param SortersConfigurator    $sortersConfigurator
     * @param RowActionsConfigurator $rowActionsConfigurator
     */
    public function __construct(
        ContextConfigurator $contextConfigurator,
        ColumnsConfigurator $columnsConfigurator,
        FiltersConfigurator $filtersConfigurator,
        SortersConfigurator $sortersConfigurator,
        RowActionsConfigurator $rowActionsConfigurator
    ) {
        parent::__construct($contextConfigurator, $columnsConfigurator, $filtersConfigurator, $sortersConfigurator);
        $this->rowActionsConfigurator = $rowActionsConfigurator;
    }

    /**
     * {@inheritdoc}
     */
    public function buildBefore(BuildBefore $event)
    {
        parent::buildBefore($event);

        $datagridConfig = $event->getConfig();
        $this->getRowActionsConfigurator()->configure($datagridConfig);
    }

    /**
     * @return RowActionsConfigurator
     */
    protected function getRowActionsConfigurator()
    {
        return $this->rowActionsConfigurator;
    }
}
