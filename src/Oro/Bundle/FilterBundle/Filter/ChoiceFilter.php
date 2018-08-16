<?php

namespace Oro\Bundle\FilterBundle\Filter;

use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Doctrine\Common\Collections\Collection;
use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Oro\Bundle\FilterBundle\Form\Type\Filter\ChoiceFilterType;
use Oro\Bundle\FilterBundle\Form\Type\Filter\FilterType;
use Symfony\Component\Form\ChoiceList\View\ChoiceGroupView;
use Symfony\Component\Form\ChoiceList\View\ChoiceView;

class ChoiceFilter extends AbstractFilter
{
    /**
     * {@inheritdoc}
     */
    protected function getFormType()
    {
        return ChoiceFilterType::class;
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
        $formView = $this->getForm()->createView();
        $fieldView = $formView->children['value'];

        $choices = array_map(
            function ($choice) {
                if ($choice instanceof ChoiceView) {
                    return [
                        'label' => $choice->label,
                        'value' => $choice->value
                    ];
                }

                if ($choice instanceof ChoiceGroupView) {
                    return [
                        'label' => $choice->label,
                        'value' => $choice->choices
                    ];
                }

                throw new \RuntimeException(sprintf(
                    'Invalid type of option for Choicefilter, expected ChoiceView or ChoiceGroupView, got "%s"',
                    get_class($choice)
                ));
            },
            $fieldView->vars['choices']
        );

        $metadata = parent::getMetadata();
        $metadata['choices'] = $choices;
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
            $value = [$value];
        }

        $data['type'] = isset($data['type']) ? $data['type'] : null;
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
        $operatorTypes = [
            ChoiceFilterType::TYPE_CONTAINS     => Operators::IN_LIST,
            ChoiceFilterType::TYPE_NOT_CONTAINS => Operators::NOT_IN_LIST,
            FilterType::TYPE_EMPTY              => Operators::IS_EMPTY,
            FilterType::TYPE_NOT_EMPTY          => Operators::IS_NOT_EMPTY,
        ];

        return isset($operatorTypes[$type]) ? $operatorTypes[$type] : Operators::IN_LIST;
    }
}
