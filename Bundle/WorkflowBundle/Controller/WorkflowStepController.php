<?php

namespace Oro\Bundle\WorkflowBundle\Controller;

use Doctrine\ORM\EntityManager;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Bundle\WorkflowBundle\Exception\WorkflowNotFoundException;
use Oro\Bundle\WorkflowBundle\Model\Step;
use Oro\Bundle\WorkflowBundle\Model\Workflow;
use Oro\Bundle\WorkflowBundle\Model\WorkflowManager;
use Oro\Bundle\WorkflowBundle\Model\WorkflowRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Symfony\Component\HttpKernel\Exception\HttpException;

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
