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
            $content = json_encode($templateJson);
        } else {
            $content = '<!-- Start Template: ' . $this->getTemplateName();
            if ($this->parent) {
                $content.= ' (Parent Template: ' . $this->parent->getTemplateName(). ')';
            }
            $content.= " -->\n";
            $content.= $templateContent;
            $content.= '<!-- End Template: ' . $this->getTemplateName() . ' -->';
        }
        return $content;
    }
}
