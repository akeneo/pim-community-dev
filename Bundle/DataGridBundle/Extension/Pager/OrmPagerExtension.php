<?php

namespace Oro\Bundle\DataGridBundle\Extension\Pager;

use Oro\Bundle\DataGridBundle\Datasource\OrmDatasource;
use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Oro\Bundle\DataGridBundle\Extension\ExtensionVisitorInterface;
use Oro\Bundle\DataGridBundle\Extension\Pager\Orm\PagerInterface;

class OrmPagerExtension implements ExtensionVisitorInterface
{
    const PAGER_KEY   = 'pager';
    const ENABLED_KEY = 'enabled';

    /** @var PagerInterface */
    protected $pager;

    public function __construct(PagerInterface $pager)
    {
        $this->pager = $pager;
    }

    /**
     * {@inheritDoc}
     */
    public function isApplicable(array $config)
    {
        $enabled = $config[OrmDatasource::SOURCE_KEY][OrmDatasource::TYPE_KEY] == OrmDatasource::TYPE;

        if (isset($config[self::PAGER_KEY][self::ENABLED_KEY])) {
            $enabled = $enabled && (bool)$config[self::PAGER_KEY][self::ENABLED_KEY];
        }

        return $enabled;
    }

    /**
     * {@inheritDoc}
     */
    public function visitDatasource(array $config, DatasourceInterface $datasource)
    {
        // @TODO request call
        // $pagerParameters = $this->parameters->get(ParametersInterface::PAGER_PARAMETERS);
        // $this->pager->setPage(isset($pagerParameters['_page']) ? $pagerParameters['_page'] : 1);
        // $this->pager->setMaxPerPage(isset($pagerParameters['_per_page']) ? (int) $pagerParameters['_per_page'] : 10);

        $this->pager->setQuery($datasource->getQuery());
        $this->pager->setPage(2);
        $this->pager->setMaxPerPage(2);
        $this->pager->init();
    }

    /**
     * {@inheritDoc}
     */
    public function visitResult(array $config, \stdClass $result)
    {
        // TODO: Implement visitResult() method.
    }
}
