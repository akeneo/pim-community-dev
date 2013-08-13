<?php

namespace Oro\Bundle\GridBundle\Filter\ORM;

use Doctrine\DBAL\Query\QueryBuilder;

use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;

use Oro\Bundle\FilterBundle\Form\Type\Filter\SelectRowFilterType;

class SelectRowFilter extends AbstractFilter
{
    const NOT_SELECTED_VALUE = 0;
    const SELECTED_VALUE     = 1;

    /**
     * {@inheritdoc}
     */
    public function filter(ProxyQueryInterface $queryBuilder, $alias, $field, $data)
    {
        $data = $this->parseData($data);

        if ($data['value'] === null) {
            return;
        }

        if ($data['in'] === null && $data['out'] !== null && empty($data['out'])) {
            $expression = $this->getExpressionFactory()->eq(1, 1);
        }

        if ($data['out'] === null && $data['in'] !== null && empty($data['in'])) {
            $expression = $this->getExpressionFactory()->eq(0, 1);
        }

        if ($data['value'] === self::SELECTED_VALUE && !empty($data['in'])) {
            $expression = $this->getExpressionFactory()->in(
                $this->createFieldExpression($field, $alias), $data['in']);
        }

        if ($data['value'] === self::NOT_SELECTED_VALUE && !empty($data['out'])) {
            $expression = $this->getExpressionFactory()->notIn(
                $this->createFieldExpression($field, $alias), $data['out']);
        }

        if (isset($expression)) {
            $this->applyFilterToClause($queryBuilder, $expression);
        }
    }

    /**
     * Transform submitted filter data to correct format
     *
     * @param array $data
     * @return array
     */
    protected function parseData($data)
    {
        if (!(isset($data['value']) || in_array($data['value'], array(self::NOT_SELECTED_VALUE, self::SELECTED_VALUE)))) {
            $data['value'] = null;
        }

        if (isset($data['in']) && !is_array($data['in'])) {
            $data['in'] = explode(',', $data['in']);
        }
        if (isset($data['out']) && !is_array($data['out'])) {
            $data['out'] = explode(',', $data['out']);
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions()
    {
        return array(
            'form_type' => SelectRowFilterType::NAME
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
        } else {
            $formOptions['field_options']['choices'] = array(
                self::NOT_SELECTED_VALUE => 'Not selected',
                self::SELECTED_VALUE     => 'Selected'
            );
        }
        $formOptions['field_options']['multiple'] = false;
        $translationDomain = $this->getOption('translation_domain');
        if (null !== $translationDomain) {
            $formOptions['translation_domain'] = $translationDomain;
        }

        return array($formType, $formOptions);
    }

    /**
     * @TODO should be refactored to use listeners in collection
     *
     * @return bool
     */
    public function needCollection()
    {
        return true;
    }
}
