<?php

namespace Oro\Bundle\WorkflowBundle\Controller;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Bundle\WorkflowBundle\Model\Workflow;
use Oro\Bundle\WorkflowBundle\Model\WorkflowManager;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\UserBundle\Annotation\AclAncestor;
use Oro\Bundle\WorkflowBundle\Model\DoctrineHelper;
use Oro\Bundle\WorkflowBundle\Exception\NotManageableEntityException;

class WidgetController extends Controller
{
    /**
     * @Route("/step/edit/item/{workflowItemId}", name="oro_workflow_widget_step_form")
     * @ParamConverter("workflowItem", options={"id"="workflowItemId"})
     * @Template
     * @AclAncestor("oro_workflow")
     */
    public function stepFormAction(WorkflowItem $workflowItem)
    {
        //$showStepName = $this->getRequest()->get('stepName', 'closed');
        $showStepName = $this->getRequest()->get('stepName', $workflowItem->getCurrentStepName());

        /** @var WorkflowManager $workflowManager */
        $workflowManager = $this->get('oro_workflow.manager');
        $workflow = $workflowManager->getWorkflow($workflowItem);
        $workflowData = $workflowItem->getData();
        $displayStep = $workflow->getStep($showStepName);
        if (!$displayStep) {
            throw new BadRequestHttpException(sprintf('There is no step "%s"', $showStepName));
        }
        $currentStep = $workflow->getStep($workflowItem->getCurrentStepName());
        if (!$currentStep) {
            throw new BadRequestHttpException(sprintf('There is no step "%s"', $workflowItem->getCurrentStepName()));
        }

        $stepForm = $this->createForm(
            $displayStep->getFormType(),
            $workflowData,
            array('stepName' => $showStepName, 'workflowItem' => $workflowItem)
        );

        $saved = false;
        if ($this->getRequest()->isMethod('POST')) {
            $stepForm->submit($this->getRequest());

            if ($stepForm->isValid()) {
                $workflowItem->setUpdated();
                $this->getEntityManager()->flush();

                $saved = true;
            }
        }

        return array(
            'saved' => $saved,
            'workflow' => $workflow,
            'steps' => $workflow->getOrderedSteps(),
            'displayStep' => $displayStep,
            'currentStep' => $currentStep,
            'form' => $stepForm->createView(),
            'workflowItem' => $workflowItem,
        );
    }

    /**
     * @Route("/buttons/entity/{entityClass}/{entityId}", name="oro_workflow_widget_buttons_entity")
     * @Template
     * @AclAncestor("oro_workflow")
     */
    public function entityButtonsAction($entityClass, $entityId)
    {
        $entity = $this->getEntityReference($entityClass, $entityId);
        $workflowName = $this->getRequest()->get('workflowName');

        /** @var WorkflowManager $workflowManager */
        $workflowManager = $this->get('oro_workflow.manager');
        $existingWorkflowItems = $workflowManager->getWorkflowItemsByEntity($entity, $workflowName);
        $newWorkflows = $workflowManager->getApplicableWorkflows($entity, $existingWorkflowItems, $workflowName);

        $transitionsData = array();
        foreach ($newWorkflows as $workflow) {
            $transitions = $workflowManager->getAllowedStartTransitions($workflow, $entity);
            foreach ($transitions as $transition) {
                $transitionsData[] = array(
                    'workflow' => $workflowManager->getWorkflow($workflow),
                    'transition' => $transition,
                );
            }
        }

        /** @var WorkflowItem $workflowItem */
        foreach ($existingWorkflowItems as $workflowItem) {
            $transitions = $workflowManager->getAllowedTransitions($workflowItem);
            foreach ($transitions as $transition) {
                $transitionsData[] = array(
                    'workflow' => $workflowManager->getWorkflow($workflowItem),
                    'workflowItem' => $workflowItem,
                    'transition' => $transition,
                );
            }
        }

        return array(
            'entity_class' => $entityClass,
            'entity_id' => $entityId,
            'transitionsData' => $transitionsData
        );
    }


    /**
     * @Route("/buttons/wizard/{workflowItemId}", name="oro_workflow_widget_buttons_wizard")
     * @ParamConverter("workflowItem", options={"id"="workflowItemId"})
     * @Template
     * @AclAncestor("oro_workflow")
     */
    public function wizardButtonsAction(WorkflowItem $workflowItem)
    {
        /** @var WorkflowManager $workflowManager */
        $workflowManager = $this->get('oro_workflow.manager');
        $workflow = $workflowManager->getWorkflow($workflowItem);

        $currentStep = $workflow->getStep($workflowItem->getCurrentStepName());
        $transitionsData = array();
        foreach ($currentStep->getAllowedTransitions() as $transitionName) {
            $transitionsData[] = array(
                'workflow' => $workflowManager->getWorkflow($workflowItem),
                'workflowItem' => $workflowItem,
                'transition' => $workflow->getTransition($transitionName),
            );
        }

        return array(
            'transitionsData' => $transitionsData,
        );
    }

    /**
     * @Route("/workflow_items/{entityClass}/{entityId}", name="oro_workflow_widget_workflow_items")
     * @Template
     * @AclAncestor("oro_workflow")
     */
    public function workflowItemsAction($entityClass, $entityId)
    {
        $entity = $this->getEntityReference($entityClass, $entityId);
        $workflowType = $this->getRequest()->get('workflowType', Workflow::TYPE_WIZARD);

        /** @var WorkflowManager $workflowManager */
        $workflowManager = $this->get('oro_workflow.manager');
        $workflowItems = $workflowManager->getWorkflowItemsByEntity($entity, null, $workflowType);

        $workflowItemsData = array();
        /** @var WorkflowItem $workflowItem */
        foreach ($workflowItems as $workflowItem) {
            $transitions = $workflowManager->getAllowedTransitions($workflowItem);
            if ($transitions) {
                $workflow = $workflowManager->getWorkflow($workflowItem);
                $workflowItemsData[] = array(
                    'workflow' => $workflowManager->getWorkflow($workflowItem),
                    'workflowItem' => $workflowItem,
                    'currentStep' => $workflow->getStep($workflowItem->getCurrentStepName()),
                    'transitions' => $transitions
                );
            }
        }

        return array(
            'entity_class' => $entityClass,
            'entity_id' => $entityId,
            'workflows_items_data' => $workflowItemsData
        );
    }

    /**
     * Try to get reference to entity
     *
     * @param string $entityClass
     * @param mixed $entityId
     * @throws BadRequestHttpException
     * @return mixed
     */
    protected function getEntityReference($entityClass, $entityId)
    {
        /** @var DoctrineHelper $doctrineHelper */
        $doctrineHelper = $this->get('oro_workflow.doctrine_helper');
        try {
            $entity = $doctrineHelper->getEntityReference($entityClass, $entityId);
        } catch (NotManageableEntityException $e) {
            throw new BadRequestHttpException($e->getMessage(), $e);
        }

        return $entity;
    }

    /**
     * @return EntityManager
     */
    protected function getEntityManager()
    {
        return $this->getDoctrine()->getManagerForClass('OroWorkflowBundle:WorkflowItem');
    }
}
