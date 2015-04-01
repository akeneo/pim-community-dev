<?php

namespace Pim\Bundle\DataGridBundle\EventListener;

use Doctrine\Common\Cache\Cache;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Pim\Bundle\DataGridBundle\Datagrid\Product\ColumnsConfigurator;
use Pim\Bundle\DataGridBundle\Datagrid\Product\ConfiguratorInterface;
use Pim\Bundle\DataGridBundle\Datagrid\Product\ContextConfigurator;
use Pim\Bundle\DataGridBundle\Datagrid\Product\FiltersConfigurator;
use Pim\Bundle\DataGridBundle\Datagrid\Product\SortersConfigurator;

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

    /** @var  Cache */
    protected $cache;

    /**
     * Constructor
     *
     * @param ContextConfigurator $contextConfigurator
     * @param ColumnsConfigurator $columnsConfigurator
     * @param FiltersConfigurator $filtersConfigurator
     * @param SortersConfigurator $sortersConfigurator
     * @param Cache               $cache
     */
    public function __construct(
        ContextConfigurator $contextConfigurator,
        ColumnsConfigurator $columnsConfigurator,
        FiltersConfigurator $filtersConfigurator,
        SortersConfigurator $sortersConfigurator,
        Cache $cache
    ) {
        $this->contextConfigurator = $contextConfigurator;
        $this->columnsConfigurator = $columnsConfigurator;
        $this->filtersConfigurator = $filtersConfigurator;
        $this->sortersConfigurator = $sortersConfigurator;
        $this->cache               = $cache;
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
        $cache = $this->cache;
        $datagridConfig = $event->getConfig();
        $cacheKey = ('product-grid.config');
        $cachedData = $cache->fetch($cacheKey);
        if (false === $cachedData) {
            $this->getContextConfigurator()->configure($datagridConfig);
            $this->getColumnsConfigurator()->configure($datagridConfig);
            $this->getSortersConfigurator()->configure($datagridConfig);
            $this->getFiltersConfigurator()->configure($datagridConfig);
            $cachedData = $datagridConfig->toArray();
            $cache->save($cacheKey, $cachedData, 30);
        } else {
            foreach($cachedData as $name => $value) {
                $datagridConfig->offsetSet($name, $value);
            }
        }
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
