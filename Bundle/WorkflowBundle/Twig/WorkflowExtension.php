<?php

namespace Oro\Bundle\WorkflowBundle\Twig;

use Doctrine\Common\Util\ClassUtils;
use Oro\Bundle\WorkflowBundle\Model\WorkflowRegistry;

class WorkflowExtension extends \Twig_Extension
{
    const NAME = 'oro_workflow';

    /**
     * @var WorkflowRegistry
     */
    protected $workflowRegistry;

    public function __construct(WorkflowRegistry $workflowRegistry)
    {
        $this->workflowRegistry = $workflowRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('has_workflows', array($this, 'hasWorkflows')),
        );
    }

    /**
     * Check for workflow instances
     *
     * @param string $entityClass
     * @return bool
     */
    public function hasWorkflows($entityClass)
    {
        return count($this->workflowRegistry->getWorkflowsByEntityClass($entityClass)) > 0;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }
}
