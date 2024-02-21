<?php

namespace Oro\Bundle\PimFilterBundle\Filter\ProductValue;

use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Oro\Bundle\FilterBundle\Filter\NumberFilter as OroNumberFilter;
use Oro\Bundle\FilterBundle\Form\Type\Filter\FilterType;
use Oro\Bundle\FilterBundle\Form\Type\Filter\NumberFilterType;
use Oro\Bundle\PimFilterBundle\Filter\ProductFilterUtility;
use Oro\Bundle\PimFilterBundle\Form\Type\Filter\PriceFilterType;

/**
 * Price filter
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PriceFilter extends OroNumberFilter
{
    /**
     * {@inheritdoc}
     */
    protected function getFormType()
    {
        return PriceFilterType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(FilterDatasourceAdapterInterface $ds, $data)
    {
        $data = $this->parseData($data);
        if (!$data) {
            return;
        }

        $operator = $this->getOperator($data['type']);
        $data['amount'] = $data['value'];
        unset($data['value']);
        unset($data['type']);

        $this->util->applyFilter(
            $ds,
            $this->get(ProductFilterUtility::DATA_NAME_KEY),
            $operator,
            $data
        );

        return true;
    }

    /**
     * Overriden to validate currency option
     *
     * {@inheritdoc}
     */
    public function parseData($data)
    {
        $data['type'] = isset($data['type']) ? $data['type'] : null;

        if (!is_array($data)
            || !array_key_exists('value', $data)
            || (
                !is_numeric($data['value']) &&
                !in_array($data['type'], [FilterType::TYPE_EMPTY, FilterType::TYPE_NOT_EMPTY])
            )) {
            return false;
        }

        if (!is_array($data) || !array_key_exists('currency', $data) || !is_string($data['currency'])) {
            return false;
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata()
    {
        $metadata = parent::getMetadata();

        $formView = $this->getForm()->createView();
        $metadata['currencies'] = $formView->vars['currency_choices'];

        return $metadata;
    }

    /**
     * {@inheritdoc}
     */
    public function getOperator($type)
    {
        $operatorTypes = [
            NumberFilterType::TYPE_EQUAL         => Operators::EQUALS,
            NumberFilterType::TYPE_GREATER_EQUAL => Operators::GREATER_OR_EQUAL_THAN,
            NumberFilterType::TYPE_GREATER_THAN  => Operators::GREATER_THAN,
            NumberFilterType::TYPE_LESS_EQUAL    => Operators::LOWER_OR_EQUAL_THAN,
            NumberFilterType::TYPE_LESS_THAN     => Operators::LOWER_THAN,
            FilterType::TYPE_EMPTY               => Operators::IS_EMPTY,
            FilterType::TYPE_NOT_EMPTY           => Operators::IS_NOT_EMPTY
        ];

        if (!isset($operatorTypes[$type])) {
            throw new \InvalidArgumentException(sprintf('Operator "%s" is undefined', $type));
        }

        return $operatorTypes[$type];
    }
}
