<?php

namespace Oro\Bundle\FilterBundle\Twig;

class RenderHeaderExtension extends AbstractExtension
{
    /**
     * Extension name
     */
    const NAME = 'oro_filter_render_header';

    /**
     * Block with required JS files
     */
    const HEADER_JAVASCRIPT = 'oro_filter_header_javascript';

    /**
     * Block with required CSS files
     */
    const HEADER_STYLESHEET = 'oro_filter_header_stylesheet';

    /**
     * @return array
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction(
                'oro_filter_render_header_javascript',
                [$this, 'renderHeaderJavascript'],
                $this->defaultFunctionOptions
            ),
            new \Twig_SimpleFunction(
                'oro_filter_render_header_stylesheet',
                [$this, 'renderHeaderStylesheet'],
                $this->defaultFunctionOptions
            ),
        ];
    }

    /**
     * Render specific block from template
     *
     * @param \Twig_Environment $environment
     * @param string $blockName
     * @param array $context
     * @return string
     */
    protected function renderTemplateBlock(\Twig_Environment $environment, $blockName, $context = [])
    {
        /** @var $template \Twig_Template */
        $template = $environment->loadTemplate($this->templateName);
        return $template->renderBlock($blockName, $context);
    }

    /**
     * @param \Twig_Environment $environment
     * @return string
     */
    public function renderHeaderJavascript(\Twig_Environment $environment)
    {
        return $this->renderTemplateBlock($environment, self::HEADER_JAVASCRIPT);
    }

    /**
     * @param \Twig_Environment $environment
     * @return string
     */
    public function renderHeaderStylesheet(\Twig_Environment $environment)
    {
        return $this->renderTemplateBlock($environment, self::HEADER_STYLESHEET);
    }
}
