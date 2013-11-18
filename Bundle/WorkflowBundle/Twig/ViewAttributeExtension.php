<?php

namespace Oro\Bundle\WorkflowBundle\Twig;

use Oro\Bundle\WorkflowBundle\Model\ContextAccessor;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;

class ViewAttributeExtension extends \Twig_Extension
{
    /**
     * @var ContextAccessor
     */
    protected $contextAccessor;

    /**
     * @var string[]
     */
    protected $templateNames = array();

    /**
     * @var \Twig_Template[]
     */
    protected $loadedTemplates;

    /**
     * @var string[]
     */
    protected $templatesCache = array();

    /**
     * @param ContextAccessor $contextAccessor
     * @param array $templateNames
     */
    public function __construct(ContextAccessor $contextAccessor, array $templateNames)
    {
        $this->contextAccessor = $contextAccessor;
        $this->templateNames = $templateNames;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction(
                'oro_workflow_render_view_attribute_row',
                array($this, 'renderViewAttributeRow'),
                array('is_safe' => array('html'), 'needs_environment' => true)
            ),
            new \Twig_SimpleFunction(
                'oro_workflow_render_view_attribute_value',
                array($this, 'renderViewAttributeValue'),
                array('is_safe' => array('html'), 'needs_environment' => true)
            ),
            new \Twig_SimpleFunction(
                'oro_workflow_get_value_by_path',
                array($this, 'getValueByPropertyPath'),
                array('is_safe' => true)
            ),
        );
    }

    /**
     * Render Workflow Item's view attribute row: label with value
     *
     * @param \Twig_Environment $environment
     * @param WorkflowItem $workflowItem
     * @param array $viewAttribute
     * @return string
     */
    public function renderViewAttributeRow(
        \Twig_Environment $environment,
        WorkflowItem $workflowItem,
        array $viewAttribute
    ) {
        return $this->renderViewAttributeBlock(
            'workflow_view_attribute_row',
            $environment,
            $workflowItem,
            $viewAttribute
        );
    }

    /**
     * Render Workflow Item's view attribute value
     *
     * @param \Twig_Environment $environment
     * @param WorkflowItem $workflowItem
     * @param array $viewAttribute
     * @return string
     */
    public function renderViewAttributeValue(
        \Twig_Environment $environment,
        WorkflowItem $workflowItem,
        array $viewAttribute
    ) {
        return $this->renderViewAttributeBlock(
            'workflow_view_attribute_value',
            $environment,
            $workflowItem,
            $viewAttribute
        );
    }

    /**
     * Render Workflow Item's view attribute block
     *
     * @param string $blockName
     * @param \Twig_Environment $environment
     * @param WorkflowItem $workflowItem
     * @param array $viewAttribute
     * @return string
     * @throws \InvalidArgumentException
     */
    protected function renderViewAttributeBlock(
        $blockName,
        \Twig_Environment $environment,
        WorkflowItem $workflowItem,
        array $viewAttribute
    ) {
        if (!isset($viewAttribute['path'])) {
            throw new \InvalidArgumentException('Option "path" is not found in view attribute.');
        }

        if (!isset($viewAttribute['label'])) {
            throw new \InvalidArgumentException('Option "label" is not found in view attribute.');
        }

        $viewAttribute['value'] = $this->getValueByPropertyPath($workflowItem, $viewAttribute['path']);

        $viewAttribute['workflow_item'] = $workflowItem;

        $viewType = isset($viewAttribute['view_type']) ? $viewAttribute['view_type'] : null;

        /** @var \Twig_Template $template */
        list($blockName, $template) = $this->getMatchingBlockAndTemplate($environment, $blockName, $viewType);

        return $template->renderBlock(
            $blockName,
            $viewAttribute
        );
    }

    /**
     * Gets matching block name and \Twig_Template object using cache
     *
     * @param \Twig_Environment $environment
     * @param string $originalBlockName
     * @param string $viewType
     * @return array And array where first element is block name and the second is matching \Twig_Template object
     * @throws \RuntimeException
     */
    protected function getMatchingBlockAndTemplate(\Twig_Environment $environment, $originalBlockName, $viewType)
    {
        $cacheKey = $originalBlockName . ':' . $viewType;

        if (array_key_exists($cacheKey, $this->templatesCache)) {
            return $this->templatesCache[$cacheKey];
        }

        $blocksNamesFallback = array($originalBlockName);

        if ($viewType) {
            array_unshift($blocksNamesFallback, $viewType . '_' . $originalBlockName);
        }

        foreach ($blocksNamesFallback as $blockName) {
            foreach ($this->loadTemplates($environment) as $template) {
                if ($template->hasBlock($blockName)) {
                    return $this->templatesCache[$cacheKey] = array($blockName, $template);
                }
            }
        }

        throw new \RuntimeException(
            sprintf(
                'Cannot find view attribute block "%s" in templates "%s".',
                $originalBlockName,
                implode('", "', $this->templateNames)
            )
        );
    }

    /**
     * @param \Twig_Environment $environment
     * @return \Twig_Template[]
     */
    protected function loadTemplates(\Twig_Environment $environment)
    {
        if (null === $this->loadedTemplates) {
            $this->loadedTemplates = array();
            foreach (array_reverse($this->templateNames) as $templateName) {
                $this->loadedTemplates[$templateName] = $environment->loadTemplate($templateName);
            }
        }
        return $this->loadedTemplates;
    }

    /**
     * Gets value of Workflow Item by path
     *
     * @param WorkflowItem $workflowItem
     * @param \Symfony\Component\PropertyAccess\PropertyPath $path
     * @return mixed
     */
    public function getValueByPropertyPath(WorkflowItem $workflowItem, $path)
    {
        return $this->contextAccessor->getValue($workflowItem, $path);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_workflow_view_attribute';
    }
}
