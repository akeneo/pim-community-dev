<?php

namespace Oro\Bundle\FilterBundle\Extension\Orm;

use Doctrine\ORM\QueryBuilder;

use Oro\Bundle\FilterBundle\Form\Type\Filter\NumberFilterType;

class NumberFilter extends AbstractFilter
{
    /**
     * {@inheritdoc}
     */
    protected function getFormType()
    {
        return NumberFilterType::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(QueryBuilder $qb, $data)
    {
        $data = $this->parseData($data);
        if (!$data) {
            return false;
        }

        $operator = $this->getOperator($data['type']);
        $parameterName = $this->generateQueryParameterName();

        $this->applyFilterToClause(
            $qb,
            $this->createComparisonExpression($this->get(self::DATA_NAME_KEY), $operator, $parameterName)
        );

        $qb->setParameter($parameterName, $data['value']);

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
        $type = (int)$type;

        $operatorTypes = array(
            NumberFilterType::TYPE_EQUAL         => '=',
            NumberFilterType::TYPE_GREATER_EQUAL => '>=',
            NumberFilterType::TYPE_GREATER_THAN  => '>',
            NumberFilterType::TYPE_LESS_EQUAL    => '<=',
            NumberFilterType::TYPE_LESS_THAN     => '<',
        );

        return isset($operatorTypes[$type]) ? $operatorTypes[$type] : '=';
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata()
    {
        $metadata = parent::getMetadata();

        $formView = $this->getForm()->createView();
        $metadata['formatterOptions'] = $formView->vars['formatter_options'];

        return $metadata;
    }
}
