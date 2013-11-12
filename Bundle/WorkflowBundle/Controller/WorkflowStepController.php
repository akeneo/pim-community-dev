<?php

namespace Oro\Bundle\WorkflowBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

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
        $this->get('oro_workflow.http.workflow_item_validator')->validate($workflowItem);

        /** @var WorkflowManager $workflowManager */
        $workflowManager = $this->get('oro_workflow.manager');
        $workflow = $workflowManager->getWorkflow($workflowItem);

        if ($workflow->getType() != Workflow::TYPE_WIZARD) {
            throw new BadRequestHttpException(
                sprintf('Workflow type "%s" is not compatible with edit action.', $workflow->getType())
            );
        }

        $currentStep = $workflow->getStepManager()->getStep($workflowItem->getCurrentStepName());

        $data = array(
            'workflow' => $workflow,
            'currentStep' => $currentStep,
            'workflowItem' => $workflowItem,
            'entity' => $workflowItem
        );

        $customTemplate = $currentStep->getTemplate();
        if ($customTemplate) {
            return $this->render($customTemplate, $data);
        } else {
            return $data;
        }
    }
}
