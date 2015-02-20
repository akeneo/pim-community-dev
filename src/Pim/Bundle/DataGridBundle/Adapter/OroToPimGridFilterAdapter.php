<?php

namespace Pim\Bundle\DataGridBundle\Adapter;

class OroToPimGridFilterAdapter implements GridFilterAdapterInterface
{
    public function __construct()
    {

    }

    public function transform(array $params)
    {
//        $params['gridName']   = $this->request->get('gridName');
//        $params['actionName'] = $this->request->get('actionName');
//        $params['values']     = implode(',', $params['values']);
//        $params['filters']    = json_encode($params['filters']);
//        $params['dataLocale'] = $this->request->get('dataLocale', null);

//        case Operators::IN_LIST:
//        case Operators::NOT_IN_LIST:
//        case Operators::IN_CHILDREN_LIST:
//        case Operators::NOT_IN_CHILDREN_LIST:
//        case Operators::UNCLASSIFIED:
//        case Operators::IN_LIST_OR_UNCLASSIFIED:

//        Maping  numéro filter
//        Oro\Bundle\FilterBundle\Form\Type\Filter\FilterType

        $this->extractFilters($params);

    }

    protected function extractFilters($params)
    {
        $filters = json_decode($params['filters']);
    }
}
