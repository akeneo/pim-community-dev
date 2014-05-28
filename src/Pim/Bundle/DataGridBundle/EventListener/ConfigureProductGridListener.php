<?php

namespace Pim\Bundle\DataGridBundle\EventListener;

use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Pim\Bundle\DataGridBundle\Datagrid\Product\ConfiguratorInterface;
use Pim\Bundle\DataGridBundle\Datagrid\Product\ContextConfigurator;
use Pim\Bundle\DataGridBundle\Datagrid\Product\ColumnsConfigurator;
use Pim\Bundle\DataGridBundle\Datagrid\Product\SortersConfigurator;
use Pim\Bundle\DataGridBundle\Datagrid\Product\FiltersConfigurator;

/**
 * Grid listener to configure columns, filters and sorters based on product attributes and business rules
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ConfigureProductGridListener
{
    /**
     * @var ContextConfigurator
     */
    protected $contextConfigurator;

    /**
     * @var ColumnsConfigurator
     */
    protected $columnsConfigurator;

    /**
     * @var FiltersConfigurator
     */
    protected $filtersConfigurator;

    /**
     * @var SortersConfigurator
     */
    protected $sortersConfigurator;

    /**
     * Constructor
     *
     * @param ContextConfigurator $contextConfigurator
     * @param ColumnsConfigurator $columnsConfigurator
     * @param FiltersConfigurator $filtersConfigurator
     * @param SortersConfigurator $sortersConfigurator
     */
    public function __construct(
        ContextConfigurator $contextConfigurator,
        ColumnsConfigurator $columnsConfigurator,
        FiltersConfigurator $filtersConfigurator,
        SortersConfigurator $sortersConfigurator
    ) {
        $this->contextConfigurator = $contextConfigurator;
        $this->columnsConfigurator = $columnsConfigurator;
        $this->filtersConfigurator = $filtersConfigurator;
        $this->sortersConfigurator = $sortersConfigurator;
    }

    /**
     * Configure product columns, filters, sorters dynamically
     *
     * @param BuildBefore $event
     *
     * @throws \LogicException
     */
    public function buildBefore(BuildBefore $event)
    {
        $datagridConfig = $event->getConfig();

        $this->getContextConfigurator()->configure($datagridConfig);
        $this->getColumnsConfigurator()->configure($datagridConfig);
        $this->getSortersConfigurator()->configure($datagridConfig);
        $this->getFiltersConfigurator()->configure($datagridConfig);
    }

    /**
     * @return ConfiguratorInterface
     */
    protected function getContextConfigurator()
    {
        return $this->contextConfigurator;
    }

    /**
     * @return ConfiguratorInterface
     */
    protected function getColumnsConfigurator()
    {
        return $this->columnsConfigurator;
    }

    /**
     * @return ConfiguratorInterface
     */
    protected function getSortersConfigurator()
    {
        return $this->sortersConfigurator;
    }

    /**
     * @return ConfiguratorInterface
     */
    protected function getFiltersConfigurator()
    {
        return $this->filtersConfigurator;
    }
}
