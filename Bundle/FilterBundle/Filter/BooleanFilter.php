<?php

namespace Oro\Bundle\FilterBundle\Filter;

use Oro\Bundle\FilterBundle\Form\Type\Filter\BooleanFilterType;
use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;

class BooleanFilter extends ChoiceFilter
{
    const NULLABLE_KEY = 'nullable';

    /**
     * {@inheritdoc}
     */
    protected function getFormType()
    {
        return BooleanFilterType::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function init($name, array $params)
    {
        // static option for metadata
        $params['contextSearch'] = false;
        parent::init($name, $params);
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

        $field             = $this->get(FilterUtility::DATA_NAME_KEY);
        $compareExpression = $ds->expr()->neq($field, 'false');

        if ($this->getOr(self::NULLABLE_KEY, false)) {
            $summaryExpression = $ds->expr()->andX(
                $ds->expr()->isNotNull($field),
                $compareExpression
            );
        } else {
            $summaryExpression = $compareExpression;
        }

        switch ($data['value']) {
            case BooleanFilterType::TYPE_YES:
                $expression = $summaryExpression;
                break;
            case BooleanFilterType::TYPE_NO:
            default:
                $expression = $ds->expr()->not($summaryExpression);
                break;
        }

        $this->applyFilterToClause($ds, $expression);

        return true;
    }

    /**
     * @param mixed $data
     *
     * @return array|bool
     */
    public function parseData($data)
    {
        $allowedValues = array(BooleanFilterType::TYPE_YES, BooleanFilterType::TYPE_NO);
        if (!is_array($data)
            || !array_key_exists('value', $data)
            || !$data['value']
            || !in_array($data['value'], $allowedValues)
        ) {
            return false;
        }

        return $data;
    }
}
