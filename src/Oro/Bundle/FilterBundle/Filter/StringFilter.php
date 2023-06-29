<?php

namespace Oro\Bundle\FilterBundle\Filter;

use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Oro\Bundle\FilterBundle\Form\Type\Filter\FilterType;
use Oro\Bundle\FilterBundle\Form\Type\Filter\TextFilterType;

class StringFilter extends AbstractFilter
{
    /**
     * {@inheritDoc}
     */
    public function apply(FilterDatasourceAdapterInterface $ds, $data)
    {
        $data = $this->parseData($data);
        if (!$data) {
            return false;
        }

        $operator = $this->getOperator($data['type']);
        $parameterName = $ds->generateParameterName($this->getName());

        switch ($operator) {
            case Operators::IS_EMPTY:
                $expression = $ds->expr()->orX(
                    $ds->expr()->isNull($this->get(FilterUtility::DATA_NAME_KEY)),
                    $ds->expr()->eq($this->get(FilterUtility::DATA_NAME_KEY), $parameterName, true)
                );
                break;
            case Operators::IS_NOT_EMPTY:
                $expression = $ds->expr()->andX(
                    $ds->expr()->isNotNull($this->get(FilterUtility::DATA_NAME_KEY)),
                    $ds->expr()->neq($this->get(FilterUtility::DATA_NAME_KEY), $parameterName, true)
                );
                break;
            default:
                $expression = $ds->expr()->comparison(
                    $this->get(FilterUtility::DATA_NAME_KEY),
                    $operator,
                    $parameterName,
                    true
                );
        }

        $this->applyFilterToClause($ds, $expression);
        $ds->setParameter($parameterName, $data['value']);

        return true;
    }

    /**
     * {@inheritDoc}
     */
    protected function getFormType()
    {
        return TextFilterType::class;
    }

    protected function parseData(mixed $data): array | false
    {
        if (!is_array($data) || !array_key_exists('value', $data) || !array_key_exists('type', $data)) {
            return false;
        }

        if (in_array($data['type'], [FilterType::TYPE_EMPTY, FilterType::TYPE_NOT_EMPTY])) {
            $data['value'] = '';

            return $data;
        }

        if (null === $data['value'] || '' === $data['value']) {
            return false;
        }

        $data['type'] = $data['type'] ?? null;

        $formattedValue = \in_array($this->getOperator($data['type']), [Operators::IS_LIKE, Operators::IS_NOT_LIKE]) ?
            \addcslashes($data['value'], '_%') : $data['value'];

        $data['value'] = sprintf(
            $this->getFormatByComparisonType($data['type']),
            $formattedValue
        );

        return $data;
    }

    /**
     * Get operator string
     *
     * @param int $type
     *
     * @return string
     */
    protected function getOperator($type)
    {
        $operatorTypes = [
            TextFilterType::TYPE_CONTAINS     => Operators::IS_LIKE,
            TextFilterType::TYPE_NOT_CONTAINS => Operators::IS_NOT_LIKE,
            TextFilterType::TYPE_EQUAL        => Operators::EQUALS,
            TextFilterType::TYPE_STARTS_WITH  => Operators::IS_LIKE,
            TextFilterType::TYPE_ENDS_WITH    => Operators::IS_LIKE,
            FilterType::TYPE_EMPTY            => Operators::IS_EMPTY,
            FilterType::TYPE_NOT_EMPTY        => Operators::IS_NOT_EMPTY,
            FilterType::TYPE_IN_LIST          => Operators::IN_LIST,
        ];

        return $operatorTypes[$type] ?? 'LIKE';
    }

    protected function getFormatByComparisonType(?int $comparisonType): string
    {
        // for other than listed comparison types - use default format
        return match ($comparisonType) {
            TextFilterType::TYPE_STARTS_WITH => '%s%%',
            TextFilterType::TYPE_ENDS_WITH => '%%%s',
            TextFilterType::TYPE_CONTAINS, TextFilterType::TYPE_NOT_CONTAINS => '%%%s%%',
            default => '%s',
        };
    }
}
