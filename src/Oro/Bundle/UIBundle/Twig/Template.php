<?php

namespace Oro\Bundle\UIBundle\Twig;

use Twig_Template;

abstract class Template extends Twig_Template
{
    /**
     * Render template with a given context and adds template and parent template name to output
     *
     * @param array $context
     * @return string
     */
    public function render(array $context)
    {
        $templateContent = parent::render($context);
        $templateJson = json_decode($templateContent);
        if ($templateJson) {
            $templateJson->template_name = $this->getTemplateName();
            if (!empty($templateJson->content)) {
                $templateJson->content = $this->wrapContent($templateJson->content, true);
            }
            $content = json_encode($templateJson);
        } else {
            $content = $this->wrapContent($templateContent);
        }
        return $content;
    }

    /**
     * Wraps content into additional HTML comment tags with template name information
     *
     * @param string $originalContent
     * @param bool $forced wrapping content with comments, event if a template is not a '.html.twig'
     * @return string
     */
    protected function wrapContent($originalContent, $forced = false)
    {
        $content = $originalContent;
        $templateName = $this->getTemplateName();
        if ($forced || '.html.twig' === substr($templateName, -10)) {
            $content = '<!-- Start Template: ' . $this->getTemplateName();
            if ($this->parent) {
                $content.= ' (Parent Template: ' . $this->parent->getTemplateName(). ')';
            }
            $content.= " -->\n";
            $content.= $originalContent;
            $content.= '<!-- End Template: ' . $this->getTemplateName() . ' -->';
        }
        return $content;
    }
}
