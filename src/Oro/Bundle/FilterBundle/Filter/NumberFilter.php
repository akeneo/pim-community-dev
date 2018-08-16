<?php

namespace Oro\Bundle\FilterBundle\Filter;

use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Oro\Bundle\FilterBundle\Form\Type\Filter\NumberFilterType;

class NumberFilter extends AbstractFilter
{
    /**
     * {@inheritdoc}
     */
    protected function getFormType()
    {
        return NumberFilterType::class;
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

        $operator = $this->getOperator($data['type']);
        $parameterName = $ds->generateParameterName($this->getName());

        $this->applyFilterToClause(
            $ds,
            $ds->expr()->comparison($this->get(FilterUtility::DATA_NAME_KEY), $operator, $parameterName, true)
        );

        $ds->setParameter($parameterName, $data['value']);

        return true;
    }

    /**
     * @param mixed $data
     *
     * @return array|bool
     */
    public function parseData($data)
    {
        if (!is_array($data) || !array_key_exists('value', $data) || !is_numeric($data['value'])) {
            return false;
        }

        $data['type'] = isset($data['type']) ? $data['type'] : null;

        return $data;
    }

    /**
     * Get operator string
     *
     * @param int $type
     *
     * @return string
     */
    public function getOperator($type)
    {
        $operatorTypes = [
            NumberFilterType::TYPE_EQUAL         => Operators::EQUALS,
            NumberFilterType::TYPE_GREATER_EQUAL => Operators::GREATER_OR_EQUAL_THAN,
            NumberFilterType::TYPE_GREATER_THAN  => Operators::GREATER_THAN,
            NumberFilterType::TYPE_LESS_EQUAL    => Operators::LOWER_OR_EQUAL_THAN,
            NumberFilterType::TYPE_LESS_THAN     => Operators::LOWER_THAN,
        ];

        return isset($operatorTypes[$type]) ? $operatorTypes[$type] : '=';
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata()
    {
        $metadata = parent::getMetadata();

        $formBuilder = $this->getFormBuilder();
        $dataType = $formBuilder->getOption('data_type');
        $metadata['formatterOptions'] = $this->getFormatterOptions($dataType);

        return $metadata;
    }

    /**
     * This method has been copy/pasted from
     * Oro\Bundle\FilterBundle\Form\Type\Filter\NumberFilterType
     * because it was only available in the buildView() method.
     *
     * Since we don't build the view anymore for the metadata, we need this logic here.
     *
     * @param string $dataType
     *
     * @return array
     */
    protected function getFormatterOptions($dataType)
    {
        $formatterOptions = [];

        switch ($dataType) {
            case NumberFilterType::DATA_DECIMAL:
                $formatterOptions['decimals'] = 2;
                $formatterOptions['grouping'] = true;
                break;
            case NumberFilterType::DATA_INTEGER:
            default:
                $formatterOptions['decimals'] = 0;
                $formatterOptions['grouping'] = false;
        }

        $formatter = new \NumberFormatter(\Locale::getDefault(), \NumberFormatter::DECIMAL);

        $formatterOptions['orderSeparator'] = $formatterOptions['grouping']
            ? $formatter->getSymbol(\NumberFormatter::GROUPING_SEPARATOR_SYMBOL)
            : '';

        $formatterOptions['decimalSeparator'] = $formatter->getSymbol(\NumberFormatter::DECIMAL_SEPARATOR_SYMBOL);

        return $formatterOptions;
    }
}
