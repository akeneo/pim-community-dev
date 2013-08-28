<?php

namespace Oro\Bundle\WorkflowBundle\Controller;

use Doctrine\ORM\EntityManager;
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
        /** @var WorkflowManager $workflowManager */
        $workflowManager = $this->get('oro_workflow.manager');
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManagerForClass($entityClass);
        $entity = $em->getReference($entityClass, $entityId);
        $existingWorkflowItems = $workflowManager->getWorkflowItemsByEntity($entity);
        $newWorkflows = $workflowManager->getApplicableWorkflows($entity, $existingWorkflowItems);

        
    }
}
