<?php

namespace Oro\Bundle\FilterBundle\Twig;

use Twig\Environment;
use Twig\TwigFunction;

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
            new TwigFunction(
                'oro_filter_render_header_javascript',
                [$this, 'renderHeaderJavascript'],
                $this->defaultFunctionOptions
            ),
            new TwigFunction(
                'oro_filter_render_header_stylesheet',
                [$this, 'renderHeaderStylesheet'],
                $this->defaultFunctionOptions
            ),
        ];
    }

    /**
     * Render specific block from template
     *
     * @param Environment $environment
     * @param string $blockName
     * @param array $context
     */
    protected function renderTemplateBlock(Environment $environment, $blockName, $context = []): string
    {
        $template = $environment->loadTemplate(
            $environment->getTemplateClass($this->templateName),
            $this->templateName
        );

        return $template->renderBlock($blockName, $context);
    }

    public function renderHeaderJavascript(Environment $environment): string
    {
        return $this->renderTemplateBlock($environment, self::HEADER_JAVASCRIPT);
    }

    public function renderHeaderStylesheet(Environment $environment)
    {
        return $this->renderTemplateBlock($environment, self::HEADER_STYLESHEET);
    }
}
