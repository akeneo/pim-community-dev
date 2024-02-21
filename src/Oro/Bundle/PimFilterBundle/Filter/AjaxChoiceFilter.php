<?php

namespace Oro\Bundle\PimFilterBundle\Filter;

use Oro\Bundle\FilterBundle\Filter\ChoiceFilter;
use Oro\Bundle\FilterBundle\Filter\FilterUtility;
use Oro\Bundle\FilterBundle\Form\Type\Filter\FilterType;
use Oro\Bundle\PimFilterBundle\Form\Type\Filter\AjaxChoiceFilterType;
use Symfony\Component\Form\ChoiceList\View\ChoiceView;

/**
 * Choice filter with asynchronously populated options
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AjaxChoiceFilter extends ChoiceFilter
{
    /**
     * {@inheritdoc}
     */
    protected function getFormType()
    {
        return AjaxChoiceFilterType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getForm()
    {
        if (null === $this->form) {
            $this->form = $this->formFactory->create($this->getFormType(), [], $this->getFormOptions());
        }

        return $this->form;
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata()
    {
        $formView = $this->getForm()->createView();
        $choices = array_map(
            function (ChoiceView $choice) {
                return [
                    'label' => $choice->label,
                    'value' => $choice->value
                ];
            },
            $formView->vars['choices']
        );

        $defaultMetadata = [
            'name'                     => $this->getName(),
            'label'                    => ucfirst($this->name),
            FilterUtility::ENABLED_KEY => true,
            'choices'                  => $choices,
        ];

        $metadata = array_diff_key(
            $this->get(),
            array_flip($this->util->getExcludeParams())
        );

        $metadata = $this->mapParams($metadata);
        $metadata = array_merge($defaultMetadata, $metadata);

        $metadata[FilterUtility::TYPE_KEY] = $formView->vars['preload_choices'] ? 'ajax-choice' : 'select2-choice';

        $metadata['populateDefault'] = $formView->vars['populate_default'];
        $metadata['choiceUrl'] = $formView->vars['choice_url'];
        $metadata['choiceUrlParams'] = $formView->vars['choice_url_params'];
        $metadata['emptyChoice'] = $formView->vars['empty_choice'];

        return $metadata;
    }

    /**
     * {@inheritdoc}
     */
    protected function parseData($data)
    {
        if (isset($data['type']) && in_array($data['type'], [FilterType::TYPE_EMPTY, FilterType::TYPE_NOT_EMPTY])) {
            $data['value'] = isset($data['value']) ? $data['value'] : null;

            return $data;
        }

        return parent::parseData($data);
    }

    /**
     * Return options passed to the form factory
     *
     * @return array
     */
    protected function getFormOptions()
    {
        return array_merge(
            $this->getOr(FilterUtility::FORM_OPTIONS_KEY, []),
            ['csrf_protection' => false]
        );
    }
}
