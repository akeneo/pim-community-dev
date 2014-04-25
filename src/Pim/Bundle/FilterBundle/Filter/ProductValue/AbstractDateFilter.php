<?php

namespace Pim\Bundle\FilterBundle\Filter\ProductValue;

use Oro\Bundle\FilterBundle\Filter\AbstractDateFilter as OroAbstractDateFilter;
use Oro\Bundle\FilterBundle\Form\Type\Filter\FilterType;
use Oro\Bundle\FilterBundle\Form\Type\Filter\DateRangeFilterType;
use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Pim\Bundle\FilterBundle\Filter\ProductFilterUtility;

/**
 * Date filter
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractDateFilter extends OroAbstractDateFilter
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

        $this->applyFilterDependingOnType(
            $type,
            $ds,
            $dateStartValue,
            $dateEndValue,
            $this->get(ProductFilterUtility::DATA_NAME_KEY)
        );

        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function isValidData($data)
    {
        if (!is_array($data) || !array_key_exists('value', $data) || !is_array($data['value'])) {
            return false;
        }

        // Empty operator does not need any value
        if (isset($data['type']) && FilterType::TYPE_EMPTY === $data['type']) {
            return true;
        }

        if (!isset($data['value']['start']) && !isset($data['value']['end'])) {
            return false;
        }

        // check start date
        if (isset($data['value']['start']) && !$data['value']['start'] instanceof \DateTime) {
            return false;
        }

        // check end date
        if (isset($data['value']['end']) && !$data['value']['end'] instanceof \DateTime) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function applyFilterDependingOnType($type, $ds, $dateStartValue, $dateEndValue, $fieldName)
    {
        switch ($type) {
            case DateRangeFilterType::TYPE_MORE_THAN:
                $this->applyFilterByAttributeLessMore($ds, $dateStartValue, $fieldName, false);
                break;
            case DateRangeFilterType::TYPE_LESS_THAN:
                $this->applyFilterByAttributeLessMore($ds, $dateEndValue, $fieldName, true);
                break;
            case DateRangeFilterType::TYPE_NOT_BETWEEN:
                $this->applyFilterByAttributeNotBetween($ds, $dateStartValue, $dateEndValue, $fieldName);
                break;
            case FilterType::TYPE_EMPTY:
                $this->util->applyFilterByAttribute(
                    $ds,
                    $this->get(ProductFilterUtility::DATA_NAME_KEY),
                    null,
                    'EMPTY'
                );
                break;
            default:
            case DateRangeFilterType::TYPE_BETWEEN:
                $this->applyFilterByAttributeBetween($ds, $dateStartValue, $dateEndValue, $fieldName);
                break;
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function applyFilterByAttributeBetween($ds, $dateStartValue, $dateEndValue, $fieldName)
    {
        if ($dateStartValue && $dateEndValue) {
            $this->util->applyFilterByAttribute(
                $ds,
                $fieldName,
                array($dateStartValue, $dateEndValue),
                'BETWEEN'
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function applyFilterByAttributeLessMore($ds, $dateValue, $fieldName, $isLess)
    {
        $this->util->applyFilterByAttribute($ds, $fieldName, $dateValue, $isLess ? '<' : '>');
    }

    /**
     * {@inheritdoc}
     */
    protected function applyFilterByAttributeNotBetween($ds, $dateStartValue, $dateEndValue, $fieldName)
    {
        $values    = array();
        $operators = array();

        if ($dateStartValue) {
            $values['from']    = $dateStartValue;
            $operators['from'] = '<';
        }

        if ($dateEndValue) {
            $values['to']    = $dateEndValue;
            $operators['to'] = '>';
        }

        if ($values && $operators) {
            $this->util->applyFilterByAttribute($ds, $fieldName, $values, $operators);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function parseData($data)
    {
        if (!$this->isValidData($data)) {
            return false;
        }

        if (isset($data['value']['start'])) {
            /** @var \DateTime $startDate */
            $startDate = $data['value']['start'];
            $startDate->setTimezone(new \DateTimeZone('UTC'));
            $data['value']['start'] = $startDate->format(static::DATETIME_FORMAT);
        } else {
            $data['value']['start'] = null;
        }

        if (isset($data['value']['end'])) {
            /** @var \DateTime $endDate */
            $endDate = $data['value']['end'];
            $endDate->setTimezone(new \DateTimeZone('UTC'));
            $data['value']['end'] = $endDate->format(static::DATETIME_FORMAT);
        } else {
            $data['value']['end'] = null;
        }

        if (!isset($data['type'])) {
            $data['type'] = null;
        }

        if (!in_array(
            $data['type'],
            array(
                DateRangeFilterType::TYPE_BETWEEN,
                DateRangeFilterType::TYPE_NOT_BETWEEN,
                DateRangeFilterType::TYPE_MORE_THAN,
                DateRangeFilterType::TYPE_LESS_THAN,
                FilterType::TYPE_EMPTY
            )
        )
        ) {
            $data['type'] = DateRangeFilterType::TYPE_BETWEEN;
        }

        if ($data['type'] == DateRangeFilterType::TYPE_MORE_THAN) {
            $data['value']['end'] = null;
        } elseif ($data['type'] == DateRangeFilterType::TYPE_LESS_THAN) {
            $data['value']['start'] = null;
        }

        return array(
            'date_start' => $data['value']['start'],
            'date_end'   => $data['value']['end'],
            'type'       => $data['type']
        );
    }
}
