<?php

namespace Pim\Bundle\EnrichBundle\ViewElement\Tab;

use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Pim\Bundle\EnrichBundle\ViewElement\Checker\VisibilityCheckerInterface;

/**
 * Simple tab rendering a template
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BaseTab implements TabInterface
{
    /** @var EngineInterface */
    protected $templating;

    /** @var string */
    protected $template;

    /** @var string */
    protected $title;

    /** @var VisibilityCheckerInterface[] */
    protected $visibilityCheckers;

    /**
     * @param EngineInterface $templating
     * @param string          $template
     * @param string          $title
     */
    public function __construct(EngineInterface $templating, $template, $title)
    {
        $this->templating         = $templating;
        $this->template           = $template;
        $this->title              = $title;
        $this->visibilityCheckers = [];
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
        foreach ($this->visibilityCheckers as $checker) {
            if (false === $checker->isVisible($context)) {
                return false;
            }
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function addVisibilityChecker(VisibilityCheckerInterface $checker, array $context = [])
    {
        $checker->setContext($context);
        $this->visibilityCheckers[] = $checker;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setVisibilityCheckers(array $checkers)
    {
        $this->visibilityCheckers = $checkers;

        return $this;
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
