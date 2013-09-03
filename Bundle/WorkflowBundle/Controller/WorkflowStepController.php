<?php

namespace Oro\Bundle\WorkflowBundle\Controller;

use Doctrine\ORM\EntityManager;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Bundle\WorkflowBundle\Exception\WorkflowNotFoundException;
use Oro\Bundle\WorkflowBundle\Model\Workflow;
use Oro\Bundle\WorkflowBundle\Model\WorkflowManager;
use Oro\Bundle\WorkflowBundle\Model\WorkflowRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Oro\Bundle\UserBundle\Annotation\AclAncestor;
use Symfony\Component\HttpKernel\Exception\HttpException;

class WorkflowStepController extends Controller
{
    /**
     * @Route("/edit/{id}", name="oro_workflow_step_edit")
     * @Template
     * @AclAncestor("oro_workflow")
     */
    public function editAction(Request $request, WorkflowItem $workflowItem)
    {
        $workflow = $this->getWorkflow($workflowItem->getWorkflowName());
        $workflowData = $workflowItem->getData();
        $currentStep = $workflow->getStep($workflowItem->getCurrentStepName());
        if (!$currentStep) {
            throw new \LogicException(
                sprintf('There is no step "%s"', $workflowItem->getCurrentStepName())
            );
        }

        $stepForm = $this->createForm(
            $currentStep->getFormType(),
            $workflowData,
            array('workflow' => $workflow, 'step' => $currentStep)
        );

        if ($request->isMethod('POST')) {
            $stepForm->submit($request);

            if ($stepForm->isValid()) {
                $workflowItem->setUpdated();
                $this->getEntityManager()->flush();

                $this->get('session')->getFlashBag()->add('success', 'Workflow item data successfully saved');
            }
        }

        $data = array(
            'workflow' => $workflow,
            'currentStep' => $currentStep,
            'stepForm' => $stepForm->createView(),
            'workflowItem' => $workflowItem,
        );

        $customTemplate = $currentStep->getTemplate();
        if ($customTemplate) {
            return $this->render($customTemplate, $data);
        } else {
            return $data;
        }
    }

    /**
     * Get Workflow by name
     *
     * @param string $name
     * @return Workflow
     * @throws HttpException
     */
    protected function getWorkflow($name)
    {
        /** @var WorkflowRegistry $workflowRegistry */
        $workflowRegistry = $this->get('oro_workflow.registry');
        try {
            $workflow = $workflowRegistry->getWorkflow($name);
        } catch (WorkflowNotFoundException $e) {
            throw new HttpException(500, sprintf('Workflow "%s" not found', $name));
        }

        return $workflow;
    }

    /**
     * @return EntityManager
     */
    protected function getEntityManager()
    {
        return $this->getDoctrine()->getManagerForClass('OroWorkflowBundle:WorkflowItem');
    }
}
