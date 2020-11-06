<?php

namespace Oro\Bundle\PimDataGridBundle\EventListener;

use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Oro\Bundle\PimDataGridBundle\Datagrid\Configuration\ConfiguratorInterface;
use Oro\Bundle\PimDataGridBundle\Datagrid\Configuration\Product\ColumnsConfigurator;
use Oro\Bundle\PimDataGridBundle\Datagrid\Configuration\Product\ContextConfigurator;
use Oro\Bundle\PimDataGridBundle\Datagrid\Configuration\Product\SortersConfigurator;

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
     * @var ConfiguratorInterface
     */
    protected $filtersConfigurator;

    /**
     * @var SortersConfigurator
     */
    protected $sortersConfigurator;

    /** @var ConfiguratorInterface */
    protected $attributesConfigurator;

    /**
     * @param ContextConfigurator   $contextConfigurator
     * @param ColumnsConfigurator   $columnsConfigurator
     * @param ConfiguratorInterface $filtersConfigurator
     * @param SortersConfigurator   $sortersConfigurator
     * @param ConfiguratorInterface $attributesConfigurator
     */
    public function __construct(
        ContextConfigurator $contextConfigurator,
        ColumnsConfigurator $columnsConfigurator,
        ConfiguratorInterface $filtersConfigurator,
        SortersConfigurator $sortersConfigurator,
        ConfiguratorInterface $attributesConfigurator
    ) {
        $this->contextConfigurator = $contextConfigurator;
        $this->columnsConfigurator = $columnsConfigurator;
        $this->filtersConfigurator = $filtersConfigurator;
        $this->sortersConfigurator = $sortersConfigurator;
        $this->attributesConfigurator = $attributesConfigurator;
    }

    /**
     * Configure product columns, filters, sorters dynamically
     *
     * @param BuildBefore $event
     *
     * @throws \LogicException
     */
    public function buildBefore(BuildBefore $event): void
    {
        $datagridConfig = $event->getConfig();

        $this->getContextConfigurator()->configure($datagridConfig);
        $this->attributesConfigurator->configure($datagridConfig);
        $this->getColumnsConfigurator()->configure($datagridConfig);
        $this->getSortersConfigurator()->configure($datagridConfig);
        $this->getFiltersConfigurator()->configure($datagridConfig);
    }

    protected function getContextConfigurator(): \Oro\Bundle\PimDataGridBundle\Datagrid\Configuration\Product\ContextConfigurator
    {
        return $this->contextConfigurator;
    }

    protected function getColumnsConfigurator(): \Oro\Bundle\PimDataGridBundle\Datagrid\Configuration\Product\ColumnsConfigurator
    {
        return $this->columnsConfigurator;
    }

    protected function getSortersConfigurator(): \Oro\Bundle\PimDataGridBundle\Datagrid\Configuration\Product\SortersConfigurator
    {
        return $this->sortersConfigurator;
    }

    protected function getFiltersConfigurator(): \Oro\Bundle\PimDataGridBundle\Datagrid\Configuration\ConfiguratorInterface
    {
        return $this->filtersConfigurator;
    }
}
