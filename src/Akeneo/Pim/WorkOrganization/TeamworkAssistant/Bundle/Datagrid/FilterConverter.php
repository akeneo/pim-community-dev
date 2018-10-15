<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\TeamworkAssistant\Bundle\Datagrid;

use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionParametersParser;
use Oro\Bundle\PimDataGridBundle\Adapter\OroToPimGridFilterAdapter;
use Symfony\Component\HttpFoundation\Request;

/**
 * It crafts a fake request in order to add parameters needed to convert oro grid filters into PQB filters.
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class FilterConverter
{
    /** @var OroToPimGridFilterAdapter */
    protected $oroToPimGridFilterAdapter;

    /** @var MassActionParametersParser */
    protected $parameterParser;

    /** @var RequestParameters */
    private $requestParams;

    /**
     * @param OroToPimGridFilterAdapter  $oroToPimGridFilterAdapter
     * @param MassActionParametersParser $parameterParser
     * @param RequestParameters          $requestParams
     */
    public function __construct(
        OroToPimGridFilterAdapter $oroToPimGridFilterAdapter,
        MassActionParametersParser $parameterParser,
        RequestParameters $requestParams
    ) {
        $this->oroToPimGridFilterAdapter = $oroToPimGridFilterAdapter;
        $this->parameterParser           = $parameterParser;
        $this->requestParams = $requestParams;
    }

    /**
     * It converts oro grid filters into PQB filters.
     *
     * @param Request $request
     * @param string  $filters
     *
     * @return array
     */
    public function convert(Request $request, $filters)
    {
        $request->query->add(
            [
                'gridName'   => OroToPimGridFilterAdapter::PRODUCT_GRID_NAME,
                'inset'      => false,
                'filters'    => $filters,
            ]
        );
        $parameters = $this->parameterParser->parse($request);

        $this->requestParams->set('_filter', $parameters['filter']);

        return $this->oroToPimGridFilterAdapter->adapt($parameters);
    }
}
