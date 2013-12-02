<?php

namespace Oro\Bundle\FilterBundle\Filter;

use Doctrine\Common\Collections\Collection;
use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Symfony\Component\Form\Extension\Core\View\ChoiceView;
use Oro\Bundle\FilterBundle\Form\Type\Filter\ChoiceFilterType;

class ChoiceFilter extends AbstractFilter
{
    /**
     * {@inheritdoc}
     */
    protected function getFormType()
    {
        return ChoiceFilterType::NAME;
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

        $operator  = $this->getOperator($data['type']);
        $parameter = $ds->generateParameterName($this->getName());

        if ('IN' == $operator) {
            $expression = $ds->expr()->in($this->get(FilterUtility::DATA_NAME_KEY), $parameter, true);
        } else {
            $expression = $ds->expr()->notIn($this->get(FilterUtility::DATA_NAME_KEY), $parameter, true);
        }

        $this->applyFilterToClause($ds, $expression);
        $ds->setParameter($parameter, $data['value']);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata()
    {
        $formView  = $this->getForm()->createView();
        $fieldView = $formView->children['value'];

        $choices = array_map(
            function (ChoiceView $choice) {
                return [
                    'label' => $choice->label,
                    'value' => $choice->value
                ];
            },
            $fieldView->vars['choices']
        );


        $metadata                    = parent::getMetadata();
        $metadata['choices']         = $choices;
        $metadata['populateDefault'] = $formView->vars['populate_default'];

        if ($fieldView->vars['multiple']) {
            $metadata[FilterUtility::TYPE_KEY] = 'multichoice';
        }
        return $metadata;
    }

    /**
     * @param mixed $data
     *
     * @return array|bool
     */
    protected function parseData($data)
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
     *
     * @return string
     */
    protected function getOperator($type)
    {
        $type = (int)$type;

        $operatorTypes = array(
            ChoiceFilterType::TYPE_CONTAINS     => 'IN',
            ChoiceFilterType::TYPE_NOT_CONTAINS => 'NOT IN',
        );

        return isset($operatorTypes[$type]) ? $operatorTypes[$type] : 'IN';
    }
}
