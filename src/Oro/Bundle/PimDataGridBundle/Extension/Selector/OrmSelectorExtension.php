<?php

namespace Oro\Bundle\PimDataGridBundle\Extension\Selector;

use Oro\Bundle\DataGridBundle\Datagrid\Builder;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface as OroDatasourceInterface;
use Oro\Bundle\DataGridBundle\Extension\AbstractExtension;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Configuration as FormatterConfiguration;

/**
 * Orm selector extension
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class OrmSelectorExtension extends AbstractExtension
{
    /**
     * @var string
     */
    const COLUMN_SELECTOR_PATH = 'selector';

    /**
     * @var SelectorInterface[]
     */
    protected $selectors;

    /**
     * @var string[]
     */
    protected $eligibleDatasource = [];

    /**
     * Constructor
     *
     * @param RequestParameters $requestParams
     */
    public function __construct(RequestParameters $requestParams = null)
    {
        $this->requestParams = $requestParams;
    }

    /**
     * {@inheritdoc}
     */
    public function isApplicable(DatagridConfiguration $config)
    {
        $datasourceType = $config->offsetGetByPath(Builder::DATASOURCE_TYPE_PATH);

        return in_array($datasourceType, $this->eligibleDatasource);
    }

    /**
     * Add selector to array of available selectors
     *
     * @param string            $name
     * @param SelectorInterface $selector
     *
     * @return $this
     */
    public function addSelector($name, SelectorInterface $selector)
    {
        $this->selectors[$name] = $selector;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function visitDatasource(DatagridConfiguration $config, OroDatasourceInterface $datasource)
    {
        $selectors = $this->getSelectorsToApply($config);
        foreach ($selectors as $selector) {
            $selector->apply($datasource, $config);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getPriority()
    {
        return -400;
    }

    /**
     * @param string $datasource
     *
     * @return OrmSelectorExtension
     */
    public function addEligibleDatasource($datasource)
    {
        $this->eligibleDatasource[] = $datasource;

        return $this;
    }

    /**
     * Prepare selectors array
     *
     * @param DatagridConfiguration $config
     *
     * @return SelectorInterface[]
     */
    protected function getSelectorsToApply(DatagridConfiguration $config)
    {
        $selectors = [];
        $columnsConfig = $config->offsetGetByPath(
            sprintf('[%s]', FormatterConfiguration::COLUMNS_KEY)
        );

        foreach ($columnsConfig as $column) {
            if (isset($column[self::COLUMN_SELECTOR_PATH]) && $name = $column[self::COLUMN_SELECTOR_PATH]) {
                $selectors[$name] = $this->selectors[$name];
            }
        }

        return $selectors;
    }
}
