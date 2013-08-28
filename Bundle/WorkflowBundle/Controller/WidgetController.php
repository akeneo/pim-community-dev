<?php

namespace Oro\Bundle\WorkflowBundle\Controller;

use Doctrine\ORM\EntityManager;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Bundle\WorkflowBundle\Model\WorkflowManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Oro\Bundle\UserBundle\Annotation\AclAncestor;

class WidgetController extends Controller
{
    /**
     * @Route("/buttons/{entityClass}/{entityId}", name="oro_workflow_widget_buttons")
     * @Template
     * @AclAncestor("oro_workflow")
     */
    public function buttonsAction($entityClass, $entityId)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManagerForClass($entityClass);
        $entity = $em->getReference($entityClass, $entityId);

        /** @var WorkflowManager $workflowManager */
        $workflowManager = $this->get('oro_workflow.manager');
        $existingWorkflowItems = $workflowManager->getWorkflowItemsByEntity($entity);
        $newWorkflows = $workflowManager->getApplicableWorkflows($entity, $existingWorkflowItems);
        $startData = array();
        foreach ($newWorkflows as $workflow) {
            $transitions = $workflowManager->getAllowedStartTransitions($workflow, $entity);
            if ($transitions) {
                $startData[] = array(
                    'workflow' => $workflowManager->getWorkflow($workflow),
                    'transitions' => $transitions
                );
            }
        }

        $existingData = array();
        /** @var WorkflowItem $workflowItem */
        foreach ($existingWorkflowItems as $workflowItem) {
            $transitions = $workflowManager->getAllowedTransitions($workflowItem);
            if ($transitions) {
                $existingData[] = array(
                    'workflow' => $workflowManager->getWorkflow($workflowItem),
                    'workflowItem' => $workflowItem,
                    'transitions' => $transitions
                );
            }
        }

        return array(
            'entity_class' => $entityClass,
            'entity_id' => $entityId,
            'new_workflows_data' => $startData,
            'exisiting_workflows_data' => $existingData
        );
    }
}
