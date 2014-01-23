<?php

namespace Pim\Bundle\FilterBundle\Filter\Flexible;

use Oro\Bundle\FilterBundle\Filter\AbstractDateFilter;
use Oro\Bundle\FilterBundle\Form\Type\Filter\DateRangeFilterType;
use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;

/**
 * Flexible filter
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractFlexibleDateFilter extends AbstractDateFilter
{
    /**
     * {@inheritdoc}
     */
    public function apply(FilterDatasourceAdapterInterface $ds, $data)
    {
        $data = $this->parseData($data);
        if (!$data) {
            return false;
        }

        /** @var $dateStartValue \DateTime */
        $dateStartValue = $data['date_start'];
        /** @var $dateEndValue \DateTime */
        $dateEndValue = $data['date_end'];
        $type         = $data['type'];

        $this->applyFlexibleDependingOnType(
            $type,
            $ds,
            $dateStartValue,
            $dateEndValue,
            $this->get(FilterUtility::DATA_NAME_KEY)
        );

        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function applyFlexibleDependingOnType($type, $ds, $dateStartValue, $dateEndValue, $fieldName)
    {
        switch ($type) {
            case DateRangeFilterType::TYPE_MORE_THAN:
                $this->applyFlexibleFilterLessMore($ds, $dateStartValue, $fieldName, false);
                break;
            case DateRangeFilterType::TYPE_LESS_THAN:
                $this->applyFlexibleFilterLessMore($ds, $dateEndValue, $fieldName, true);
                break;
            case DateRangeFilterType::TYPE_NOT_BETWEEN:
                $this->applyFlexibleFilterNotBetween($ds, $dateStartValue, $dateEndValue, $fieldName);
                break;
            default:
            case DateRangeFilterType::TYPE_BETWEEN:
                $this->applyFlexibleFilterBetween($ds, $dateStartValue, $dateEndValue, $fieldName);
                break;
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function applyFlexibleFilterBetween($ds, $dateStartValue, $dateEndValue, $fieldName)
    {
        if ($dateStartValue) {
            $this->util->applyFlexibleFilter($ds, $this->getFEN(), $fieldName, $dateStartValue, '>=');
        }

        if ($dateEndValue) {
            $this->util->applyFlexibleFilter($ds, $this->getFEN(), $fieldName, $dateEndValue, '<=');
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function applyFlexibleFilterLessMore($ds, $dateValue, $fieldName, $isLess)
    {
        $this->util->applyFlexibleFilter($ds, $this->getFEN(), $fieldName, $dateValue, $isLess ? '<' : '>');
    }

    /**
     * {@inheritdoc}
     */
    protected function applyFlexibleFilterNotBetween($ds, $dateStartValue, $dateEndValue, $fieldName)
    {
        $values    = [];
        $operators = [];

        if ($dateStartValue) {
            $values['from']    = $dateStartValue;
            $operators['from'] = '<';
        }

        if ($dateEndValue) {
            $values['to']    = $dateEndValue;
            $operators['to'] = '>';
        }

        if ($values && $operators) {
            $this->util->applyFlexibleFilter($ds, $this->getFEN(), $fieldName, $values, $operators);
        }
    }

    /**
     * Returns flexible entity name
     *
     * @return string
     */
    protected function getFEN()
    {
        return $this->get(FilterUtility::FEN_KEY);
    }
}
