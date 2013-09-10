<?php

namespace Oro\Bundle\GridBundle\Filter\ORM;

use Doctrine\ORM\QueryBuilder;

use Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface;
use Oro\Bundle\FilterBundle\Form\Type\Filter\NumberFilterType;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;

class NumberFilter extends AbstractFilter
{
    /**
     * {@inheritdoc}
     */
    public function filter(ProxyQueryInterface $queryBuilder, $alias, $field, $data)
    {
        $data = $this->parseData($data);
        if (!$data) {
            return;
        }

        $operator = $this->getOperator($data['type']);

        // c.name > '1' => c.name OPERATOR :FIELDNAME
        $parameterName = $this->getNewParameterName($queryBuilder);

        $this->applyFilterToClause(
            $queryBuilder,
            $this->createCompareFieldExpression($field, $alias, $operator, $parameterName)
        );

        $queryBuilder->setParameter($parameterName, $data['value']);
    }

    /**
     * @param mixed $data
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
    public function getDefaultOptions()
    {
        return array(
            'form_type' => NumberFilterType::NAME
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getRenderSettings()
    {
        list($formType, $formOptions) = parent::getRenderSettings();

        $dataType = $this->getOption('data_type', FieldDescriptionInterface::TYPE_INTEGER);
        switch ($dataType) {
            case FieldDescriptionInterface::TYPE_DECIMAL:
            case FieldDescriptionInterface::TYPE_PERCENT:
                $formOptions['data_type'] = NumberFilterType::DATA_DECIMAL;
                break;
            case FieldDescriptionInterface::TYPE_INTEGER:
            default:
                $formOptions['data_type'] = NumberFilterType::DATA_INTEGER;
        }

        return array($formType, $formOptions);
    }
}
