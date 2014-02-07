<?php

namespace Pim\Bundle\FilterBundle\Filter;

use Oro\Bundle\FilterBundle\Filter\FilterUtility;
use Oro\Bundle\FilterBundle\Filter\ChoiceFilter;
use Pim\Bundle\FilterBundle\Form\Type\Filter\AjaxChoiceFilterType;

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
        return AjaxChoiceFilterType::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata()
    {
        $formView = $this->getForm()->createView();

        $defaultMetadata = [
            'name'                     => $this->getName(),
            'label'                    => ucfirst($this->name),
            FilterUtility::ENABLED_KEY => true,
        ];

        $metadata = array_diff_key(
            $this->get(),
            array_flip($this->util->getExcludeParams())
        );

        $metadata = $this->mapParams($metadata);
        $metadata = array_merge($defaultMetadata, $metadata);

        $metadata[FilterUtility::TYPE_KEY] = 'ajax-choice';

        $metadata['populateDefault'] = $formView->vars['populate_default'];
        $metadata['choiceUrl']       = $formView->vars['choice_url'];
        $metadata['choiceUrlParams'] = $formView->vars['choice_url_params'];

        return $metadata;
    }
}
