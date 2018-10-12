<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Permission\Bundle\Datagrid\EventListener;

use Akeneo\Pim\Permission\Bundle\Datagrid\Product\RowActionsConfigurator;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Oro\Bundle\PimDataGridBundle\Datagrid\Configuration\ConfiguratorInterface;
use Oro\Bundle\PimDataGridBundle\Datagrid\Configuration\Product\ColumnsConfigurator;
use Oro\Bundle\PimDataGridBundle\Datagrid\Configuration\Product\ContextConfigurator;
use Oro\Bundle\PimDataGridBundle\Datagrid\Configuration\Product\SortersConfigurator;
use Oro\Bundle\PimDataGridBundle\EventListener\ConfigureProductGridListener as BaseConfigureProductGridListener;

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
     * @param ConfiguratorInterface  $filtersConfigurator
     * @param SortersConfigurator    $sortersConfigurator
     * @param ConfiguratorInterface  $attributesConfigurator
     * @param RowActionsConfigurator $actionsConfigurator
     */
    public function __construct(
        ContextConfigurator $contextConfigurator,
        ColumnsConfigurator $columnsConfigurator,
        ConfiguratorInterface $filtersConfigurator,
        SortersConfigurator $sortersConfigurator,
        ConfiguratorInterface $attributesConfigurator,
        RowActionsConfigurator $actionsConfigurator = null
    ) {
        parent::__construct($contextConfigurator, $columnsConfigurator, $filtersConfigurator, $sortersConfigurator, $attributesConfigurator);
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
