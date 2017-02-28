<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\TeamworkAssistantBundle\Datagrid;

use Pim\Bundle\DataGridBundle\Adapter\OroToPimGridFilterAdapter;
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

    /**
     * @param OroToPimGridFilterAdapter $oroToPimGridFilterAdapter
     */
    public function __construct(OroToPimGridFilterAdapter $oroToPimGridFilterAdapter)
    {
        $this->oroToPimGridFilterAdapter = $oroToPimGridFilterAdapter;
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
                'actionName' => 'mass_edit', //Fake mass action, needed for the grid filter adapter.
                'inset'      => false,
                'filters'    => $filters,
            ]
        );

        return $this->oroToPimGridFilterAdapter->adapt($request);
    }
}
