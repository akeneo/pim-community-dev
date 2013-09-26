<?php

namespace Oro\Bundle\WorkflowBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Bundle\WorkflowBundle\Model\Workflow;
use Oro\Bundle\WorkflowBundle\Model\WorkflowManager;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

class WorkflowStepController extends Controller
{
    /**
     * @Route("/edit/{id}", name="oro_workflow_step_edit")
     * @Template
     * @AclAncestor("oro_workflow")
     */
    public function editAction(WorkflowItem $workflowItem)
    {
        /** @var WorkflowManager $workflowManager */
        $workflowManager = $this->get('oro_workflow.manager');
        if (!$workflowManager->isAllManagedEntitiesSpecified($workflowItem)) {
            // there is no need to remove workflow item to allow debugging of this case
            throw new NotFoundHttpException('Managed entities for workflow item not found');
        }

        $workflow = $workflowManager->getWorkflow($workflowItem);
        $currentStep = $workflow->getStep($workflowItem->getCurrentStepName());

        $data = array(
            'workflow' => $workflow,
            'currentStep' => $currentStep,
            'workflowItem' => $workflowItem
        );

        $customTemplate = $currentStep->getTemplate();
        if ($customTemplate) {
            return $this->render($customTemplate, $data);
        } else {
            return $data;
        }
    }
}
