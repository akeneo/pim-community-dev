<?php

namespace Oro\Bundle\FormBundle\Twig;

use Symfony\Component\Form\FormView;

class JsValidationExtension extends \Twig_Extension
{
    const DEFAULT_TEMPLATE = 'OroFormBundle:Form:fields.html.twig';
    const BLOCK_NAME = 'oro_form_js_validation';

    /**
     * @var string
     */
    protected $templateName;

    /**
     * @var array
     */
    protected $defaultOptions;

    /**
     * @param string $templateName
     * @param array $defaultOptions
     */
    public function __construct($templateName = self::DEFAULT_TEMPLATE, $defaultOptions = array())
    {
        $this->templateName = $templateName;
        $this->defaultOptions = $defaultOptions;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction(
                'oro_form_js_validation',
                array($this, 'renderFormJsValidationBlock'),
                array('needs_environment' => true, 'is_safe' => array('html'))
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_form_js_validation';
    }

    /**
     * Renders "oro_form_js_validation" block with init script for JS validation of form.
     *
     * @param \Twig_Environment $environment
     * @param FormView $view
     * @param array $options
     * @return string
     * @throws \RuntimeException
     */
    public function renderFormJsValidationBlock(\Twig_Environment $environment, FormView $view, $options = array())
    {
        $options = array_merge($this->defaultOptions, $options);

        /** @var \Twig_Template $template */
        $template = $environment->loadTemplate($this->templateName);
        if (!$template->hasBlock(self::BLOCK_NAME)) {
            throw new \RuntimeException(
                sprintf('Block "%s" is not found in template "%s".', self::BLOCK_NAME, $this->templateName)
            );
        }

        return $template->renderBlock(
            self::BLOCK_NAME,
            array(
                'form' => $view,
                'options' => $options,
                'js_options' => $this->filterJsOptions($options)
            )
        );
    }

    /**
     * Exclude object values.
     *
     * @param array $options
     * @return array
     */
    protected function filterJsOptions(array $options)
    {
        foreach ($options as $name => $value) {
            if (is_object($value)) {
                unset($options[$name]);
            }
            if (is_array($value)) {
                $options[$name] = $this->filterJsOptions($value);
            }
        }
        return $options;
    }
}
