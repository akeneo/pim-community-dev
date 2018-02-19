<?php

namespace Oro\Bundle\DataGridBundle\Extension\MassAction;

use Symfony\Component\HttpFoundation\Request;

class MassActionParametersParser
{
    /**
     * @param Request $request
     *
     * @return array
     */
    public function parse(Request $request)
    {
        $inset = $request->get('inset', true);
        $inset = !empty($inset) && 'false' !== $inset;

        $all = $request->request->all();
        $values = isset($all['itemIds']) ? explode(',', $all['itemIds']) : [];

        $filters = $request->get('filters', null);
        if (is_string($filters)) {
            $filters = json_decode($filters, true);
        }
        if (!$filters) {
            $filters = [];
        }

        $actionName = $request->get('actionName');
        $gridName = $request->get('gridName');
        $dataLocale = $request->query->get('dataLocale');
        $dataScope = isset($filters['scope']) ? $filters['scope'] : null;
        $gridParams = $request->query->get($request->get('gridName'));
        $sort = isset($gridParams['_sort_by']) ? $gridParams['_sort_by'] : null;

        return [
            'inset'      => $inset,
            'values'     => $values,
            'filters'    => $filters,
            'actionName' => $actionName,
            'gridName'   => $gridName,
            'dataLocale' => $dataLocale,
            'dataScope'  => $dataScope,
            'sort'       => $sort
        ];
    }
}
