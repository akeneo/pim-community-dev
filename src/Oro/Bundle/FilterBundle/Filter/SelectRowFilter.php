<?php

namespace Oro\Bundle\FilterBundle\Filter;

use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Oro\Bundle\FilterBundle\Form\Type\Filter\SelectRowFilterType;

class SelectRowFilter extends ChoiceFilter
{
    /**
     * {@inheritdoc}
     */
    protected function getFormType()
    {
        return SelectRowFilterType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(FilterDatasourceAdapterInterface $ds, $data)
    {
        $data = $this->parseData($data);
        if (!$data) {
            return false;
        }

        $expression = false;
        switch (true) {
            case $data['in'] === null && $data['out'] !== null && empty($data['out']):
                $expression = $ds->expr()->eq(1, 1);
                break;
            case $data['out'] === null && $data['in'] !== null && empty($data['in']):
                $expression = $ds->expr()->eq(0, 1);
                break;
            case !empty($data['in']):
                $expression = $ds->expr()->in($this->get(FilterUtility::DATA_NAME_KEY), $data['in']);
                break;
            case !empty($data['out']):
                $expression = $ds->expr()->notIn($this->get(FilterUtility::DATA_NAME_KEY), $data['out']);
                break;
        }
        if (!$expression) {
            return false;
        }

        $this->applyFilterToClause($ds, $expression);

        return true;
    }

    /**
     * Transform submitted filter data to correct format
     *
     * @param array $data
     *
     * @return array
     */
    protected function parseData($data)
    {
        $expectedChoices = [SelectRowFilterType::NOT_SELECTED_VALUE, SelectRowFilterType::SELECTED_VALUE];
        if (empty($data['value'])
            || !in_array($data['value'], $expectedChoices)) {
            return false;
        }

        if (isset($data['in']) && !is_array($data['in'])) {
            $data['in'] = explode(',', $data['in']);
        }
        if (isset($data['out']) && !is_array($data['out'])) {
            $data['out'] = explode(',', $data['out']);
        }

        return $data;
    }
}
