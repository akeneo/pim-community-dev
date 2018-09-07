<?php

namespace Oro\Bundle\PimDataGridBundle\EventListener;

use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Oro\Bundle\DataGridBundle\Event\BuildAfter;
use Oro\Bundle\PimDataGridBundle\Datasource\Datasource;
use Oro\Bundle\PimDataGridBundle\Datasource\FamilyDatasource;
use Oro\Bundle\PimDataGridBundle\Datasource\RepositoryDatasource;

/**
 * A listener to inject locale code into translatable entity grids
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddLocaleCodeToGridListener
{
    /** @staticvar string */
    const LOCALE_PARAMETER = '[options][locale_parameter]';

    /** @var RequestParameters */
    protected $requestParams;

    /**
     * @param RequestParameters $requestParams
     */
    public function __construct(RequestParameters $requestParams)
    {
        $this->requestParams = $requestParams;
    }

    /**
     * Add locale parameter to the querybuilder
     *
     * @param BuildAfter $event
     */
    public function onBuildAfter(BuildAfter $event)
    {
        $datagrid = $event->getDatagrid();
        $datasource = $datagrid->getDatasource();
        $config = $datagrid->getAcceptor()->getConfig();

        $localeParameter = $config->offsetGetByPath(self::LOCALE_PARAMETER);

        if ($localeParameter
            && (
                $datasource instanceof Datasource ||
                $datasource instanceof RepositoryDatasource ||
                $datasource instanceof FamilyDatasource
            )
        ) {
            $queryBuilder = $datasource->getQueryBuilder();
            $queryBuilder->setParameter($localeParameter, $this->requestParams->get($localeParameter, null));
        }
    }
}
