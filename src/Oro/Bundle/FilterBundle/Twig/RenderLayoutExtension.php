<?php

namespace Oro\Bundle\FilterBundle\Twig;

use Symfony\Component\Form\FormView;
use Symfony\Component\Form\Extension\Core\View\ChoiceView;

class RenderLayoutExtension extends AbstractExtension
{
    /**
     * Extension name
     */
    const NAME = 'oro_filter_render_layout';

    /**
     * JS block suffix
     */
    const SUFFIX = '_js';

    /**
     * @return array
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction(
                'oro_filter_render_filter_javascript',
                array($this, 'renderFilterJavascript'),
                $this->defaultFunctionOptions
            ),
        );
    }

    /**
     * Render JS code for specified filter form view
     *
     * @param \Twig_Environment $environment
     * @param FormView $formView
     * @return string
     */
    public function renderFilterJavascript(\Twig_Environment $environment, FormView $formView)
    {
        if (!$formView->vars['block_prefixes'] || !is_array($formView->vars['block_prefixes'])) {
            return '';
        }

        /** @var $template \Twig_Template */
        $template = $environment->loadTemplate($this->templateName);

        // start from the last element
        $prefixes = array_reverse($formView->vars['block_prefixes']);

        foreach ($prefixes as $prefix) {
            $blockName = $prefix . self::SUFFIX;
            if ($template->hasBlock($blockName)) {
                return $template->renderBlock(
                    $blockName,
                    array('formView' => $formView)
                );
            }
        }

        return '';
    }

    /**
     * @return array
     */
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter(
                'oro_filter_choices',
                array($this, 'getChoices')
            )
        );
    }

    /**
     * Convert array of choice views to plain array
     *
     * @param array $choices
     *
     * @return array
     */
    public function getChoices(array $choices)
    {
        $result = array();
        foreach ($choices as $choice) {
            if ($choice instanceof ChoiceView) {
                $result[] = [
                    'value' => $choice->value,
                    'label' => $choice->label
                ];
            }
        }
        return $result;
    }
}
