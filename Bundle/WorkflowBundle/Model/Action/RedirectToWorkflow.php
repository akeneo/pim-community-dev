<?php

namespace Oro\Bundle\WorkflowBundle\Model\Action;

use Symfony\Component\PropertyAccess\PropertyPath;

use Oro\Bundle\WorkflowBundle\Exception\InvalidParameterException;

use Oro\Bundle\WorkflowBundle\Model\Condition\ConditionInterface;
use Oro\Bundle\WorkflowBundle\Model\Action\Redirect;

class RedirectToWorkflow implements ActionInterface
{
    /**
     * @var Redirect
     */
    protected $redirectAction;

    /**
     * @param Redirect $redirectAction
     */
    public function __construct(Redirect $redirectAction)
    {
        $this->redirectAction = $redirectAction;
    }

    /**
     * {@inheritDoc}
     */
    public function execute($context)
    {
        $this->redirectAction->execute($context);
    }

    /**
     * Allowed options:
     *  - workflow_item|0 - attribute that contains WorklfowItem to perform redirect
     *
     * {@inheritDoc}
     */
    public function initialize(array $options)
    {
        if (empty($options['workflow_item']) && empty($options[0])) {
            throw new InvalidParameterException('Workflow item parameter is required');
        }

        if (!empty($options['workflow_item'])) {
            $workflowItemProperty = $options['workflow_item'];
            unset($options['workflow_item']);
        } else {
            $workflowItemProperty = $options[0];
            unset($options[0]);
        }

        if (!$workflowItemProperty instanceof PropertyPath) {
            throw new InvalidParameterException('Workflow item must be valid property definition');
        }

        // route parameters to generate URL that leads to workflow item edit page
        $options['route'] = 'oro_workflow_step_edit';
        $options['route_parameters'] = array(
            'id' => new PropertyPath((string)$workflowItemProperty . '.id')
        );

        $this->redirectAction->initialize($options);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setCondition(ConditionInterface $condition)
    {
        $this->redirectAction->setCondition($condition);
    }
}
