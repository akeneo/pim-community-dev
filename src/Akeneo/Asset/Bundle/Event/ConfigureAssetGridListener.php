<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Asset\Bundle\Event;

use Akeneo\Asset\Bundle\Datagrid\Configuration\RowActionsConfigurator;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Oro\Bundle\PimDataGridBundle\Datagrid\Configuration\ConfiguratorInterface;
use Oro\Bundle\PimDataGridBundle\Datagrid\Configuration\Product\ConfigurationRegistry;

/**
 * Grid listener to configure asset grid
 *
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
class ConfigureAssetGridListener
{
    /** @var DatagridConfiguration */
    protected $configuration;

    /** @var ConfigurationRegistry */
    protected $registry;

    /** @var ConfiguratorInterface */
    protected $contextConfigurator;

    /** @var RowActionsConfigurator */
    private $rowActionsConfigurator;


    public function __construct(
        RowActionsConfigurator $rowActionsConfigurator,
        ConfiguratorInterface $contextConfigurator
    ) {
        $this->contextConfigurator = $contextConfigurator;
        $this->rowActionsConfigurator = $rowActionsConfigurator;
    }

    /**
     * {@inheritdoc}
     */
    public function configure(DatagridConfiguration $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * @param BuildBefore $event
     */
    public function buildBefore(BuildBefore $event)
    {
        $datagridConfig = $event->getConfig();

        $this->rowActionsConfigurator->configure($datagridConfig);
        $this->contextConfigurator->configure($datagridConfig);
    }
}
