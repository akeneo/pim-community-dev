<?php

namespace Oro\Bundle\GridBundle\Filter\ORM;

use Oro\Bundle\FilterBundle\Form\Type\Filter\EntityFilterType;

class EntityFilter extends ChoiceFilter
{
    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions()
    {
        return array(
            'form_type' => EntityFilterType::NAME
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getRenderSettings()
    {
        list($formType, $formOptions) = parent::getRenderSettings();

        // proxy for entity form type options
        foreach (array('class', 'property', 'query_builder') as $option) {
            $optionValue = $this->getOption($option);
            if ($optionValue) {
                $formOptions['field_options'][$option] = $optionValue;
            }
        }

        $translatable = $this->getOption('translatable');
        if ($translatable) {
            $formOptions['translatable'] = $translatable;
        }

        return array($formType, $formOptions);
    }
}
