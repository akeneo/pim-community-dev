<?php

namespace Oro\Bundle\WorkflowBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * Adds custom template to use in rendering of Workflow's Step view attributes.
 * Basic template is OroWorkflowBundle:WorkflowStep:view_attributes.html.twig
 */
class AddViewAttributeTwigTemplateCompilerPass implements CompilerPassInterface
{
    const VIEW_ATTRIBUTES_TEMPLATES_PARAMETER = 'oro_workflow.twig.extension.view_attribute.templates';

    /**
     * @var string
     */
    protected $templateName;

    /**
     * @param string $templateName
     */
    public function __construct($templateName)
    {
        $this->templateName = $templateName;
    }

    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $this->addViewAttributeTwigTemplate($container);
    }

    /**
     * @param ContainerBuilder $container
     */
    protected function addViewAttributeTwigTemplate(ContainerBuilder $container)
    {
        $originTemplates = $container->getParameter(self::VIEW_ATTRIBUTES_TEMPLATES_PARAMETER);
        $originTemplates[] = $this->templateName;
        $container->setParameter(self::VIEW_ATTRIBUTES_TEMPLATES_PARAMETER, $originTemplates);
    }
}
