<?php

namespace Pim\Bundle\EnrichBundle\View\Tab;

use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;

/**
 * Simple tab rendering a template
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BaseTab implements TabInterface
{
    /**
     * @param EngineInterface $templating
     * @param string          $template
     * @param string          $title
     */
    public function __construct(EngineInterface $templating, $template, $title)
    {
        $this->templating = $templating;
        $this->template   = $template;
        $this->title      = $title;
    }

    /**
     * {@inheritdoc}
     */
    public function getContent(array $context = [])
    {
        return $this->getTemplateComment($this->template) .
            $this->templating->render($this->template, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle(array $context = [])
    {
        return $this->title;
    }

    /**
     * {@inheritdoc}
     */
    public function isVisible(array $context = [])
    {
        return true;
    }

    /**
     * Get template comment
     * @param string $templateName The template name
     *
     * @return string
     */
    protected function getTemplateComment($templateName)
    {
        return sprintf("<!-- %s -->\n", $templateName);
    }
}
