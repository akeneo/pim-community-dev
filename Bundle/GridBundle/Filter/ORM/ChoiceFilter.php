<?php

namespace Oro\Bundle\GridBundle\Filter\ORM;

use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\Common\Collections\Collection;

use Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface;
use Oro\Bundle\FilterBundle\Form\Type\Filter\ChoiceFilterType;

class ChoiceFilter extends AbstractFilter
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

        $operator  = $this->getOperator($data['type']);
        $parameter = $this->getName() . '_choices';

        if ('IN' == $operator) {
            $expression = $this->getExpressionFactory()->in(
                $this->createFieldExpression($field, $alias),
                ':' . $parameter
            );
        } else {
            $expression = $this->getExpressionFactory()->notIn(
                $this->createFieldExpression($field, $alias),
                ':' . $parameter
            );
        }

        $this->applyFilterToClause($queryBuilder, $expression);
        /** @var $queryBuilder QueryBuilder */
        $queryBuilder->setParameter($parameter, $data['value']);
    }

    /**
     * @param mixed $data
     * @return array|bool
     */
    public function parseData($data)
    {
        if (!is_array($data)
            || !array_key_exists('value', $data)
            || $data['value'] === ''
            || is_null($data['value'])
            || ((is_array($data['value']) || $data['value'] instanceof Collection) && !count($data['value']))
        ) {
            return false;
        }

        $value = $data['value'];

        if ($value instanceof Collection) {
            $value = $value->getValues();
        }
        if (!is_array($value)) {
            $value = array($value);
        }

        $data['type']  = isset($data['type']) ? $data['type'] : null;
        $data['value'] = $value;

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
            ChoiceFilterType::TYPE_CONTAINS     => 'IN',
            ChoiceFilterType::TYPE_NOT_CONTAINS => 'NOT IN',
        );

        return isset($operatorTypes[$type]) ? $operatorTypes[$type] : 'IN';
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions()
    {
        return array(
            'form_type' => ChoiceFilterType::NAME
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getRenderSettings()
    {
        list($formType, $formOptions) = parent::getRenderSettings();

        $choices = $this->getOption('choices');
        if ($choices) {
            $formOptions['field_options']['choices'] = $choices;
        }

        $multiple = $this->getOption('multiple');
        if (null !== $multiple) {
            $formOptions['field_options']['multiple'] = $multiple;
        }

        $translationDomain = $this->getOption('translation_domain');
        if (null !== $translationDomain) {
            $formOptions['translation_domain'] = $translationDomain;
        }

        return array($formType, $formOptions);
    }
}
