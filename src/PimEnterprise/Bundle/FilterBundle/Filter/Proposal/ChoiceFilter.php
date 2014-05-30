<?php

namespace PimEnterprise\Bundle\FilterBundle\Filter\Proposal;

use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;

use PimEnterprise\Bundle\FilterBundle\Filter\ProposalFilterUtility;

use Symfony\Component\Form\FormFactoryInterface;

use Pim\Bundle\FilterBundle\Filter\AjaxChoiceFilter;

class ChoiceFilter extends AjaxChoiceFilter
{
    public function apply(FilterDatasourceAdapterInterface $ds, $data)
    {
        $data = $this->parseData($data);
        if (!$data) {
            return false;
        }

        $operator  = $this->getOperator($data['type']);
        $field     = $this->get(ProposalFilterUtility::DATA_NAME_KEY);

        $this->util->applyFilter($ds, $field, $operator, $data['value']);
    }
}
