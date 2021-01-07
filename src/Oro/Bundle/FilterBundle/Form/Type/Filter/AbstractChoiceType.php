<?php

namespace Oro\Bundle\FilterBundle\Form\Type\Filter;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Contracts\Translation\TranslatorInterface;

abstract class AbstractChoiceType extends AbstractType
{
    protected TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        if (!array_key_exists('strict', $options)) {
            $options['strict'] = true;
        }

        if (!empty($view->children['value'])) {
            /** @var FormView $valueFormView */
            $valueFormView = $view->children['value'];
            if (!empty($valueFormView->vars['choices'])) {
                // get translation domain
                $translationDomain = 'messages';
                if (!empty($options['translation_domain'])) {
                    $translationDomain = $options['translation_domain'];
                } elseif (!empty($view->parent->vars['translation_domain'])) {
                    $translationDomain = $view->parent->vars['translation_domain'];
                }

                // translate choice values
                foreach ($valueFormView->vars['choices'] as $key => $choiceView) {
                    $choiceView->label = $this->translator->trans(
                        $choiceView->label,
                        [],
                        $translationDomain
                    );
                    $valueFormView->vars['choices'][$key] = $choiceView;
                }
            }
        }
    }
}
