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

        $values = $request->get('values', '');
        if (!is_array($values)) {
            $values = $values !== '' ? explode(',', $values) : [];
        }

        $filters = $request->get('filters', null);
        if (is_string($filters)) {
            $filters = json_decode($filters, true);
        }
        if (!$filters) {
            $filters = [];
        }

        return [
            'inset'   => $inset,
            'values'  => $values,
            'filters' => $filters,
        ];
    }
}
