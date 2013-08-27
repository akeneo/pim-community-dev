<?php

namespace Oro\Bundle\WorkflowBundle\Controller\Api\Rest;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\Rest\Util\Codes;
use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\Get;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Oro\Bundle\UserBundle\Annotation\AclAncestor;
use Oro\Bundle\WorkflowBundle\Exception\WorkflowNotFoundException;
use Oro\Bundle\WorkflowBundle\Model\Workflow;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Bundle\WorkflowBundle\Model\WorkflowManager;
use Oro\Bundle\WorkflowBundle\Exception\UnknownTransitionException;
use Oro\Bundle\WorkflowBundle\Exception\ForbiddenTransitionException;

/**
 * @NamePrefix("oro_api_workflow_")
 */
class WorkflowController extends FOSRestController
{
    /**
     * @Get("/start/{workflowName}/{entityClass}/{entityId}/{transitionName}")
     * @ApiDoc(description="Start workflow for entity from transition", resource=true)
     * @AclAncestor("oro_workflow")
     */
    public function startAction($workflowName, $entityClass, $entityId, $transitionName)
    {
        return $this->handleView(
            $this->view(array('workflowItemId' => 1), Codes::HTTP_OK)
        );

        /** @var WorkflowManager $workflowManager */
        /*
        $workflowManager = $this->get('oro_workflow.manager');
        $workflowItem = $workflowManager->startWorkflow(
            $workflowName,
            $entityClass,
            $entityId,
            $transitionName
        );

        return $this->redirect(
            $this->generateUrl(
                'acme_demoworkflow_workflowitem_edit',
                array('id' => $workflowItem->getId())
            )
        );
        */
    }

    /**
     * @Get("/transit/{workflowItemId}/{transitionName}", requirements={"workflowItemId"="\d+"})
     * @ParamConverter("workflowItem", options={"id"="workflowItemId"})
     * @ApiDoc(description="Perform transition for workflow item", resource=true)
     * @AclAncestor("oro_workflow")
     */
    public function transitAction(WorkflowItem $workflowItem, $transitionName)
    {
        return $this->handleView(
            $this->view(true, Codes::HTTP_OK)
        );

        /*
        try {
            $this->get('oro_workflow.manager')->transit($workflowItem, $transitionName);

            $this->get('session')->getFlashBag()->add(
                'success',
                'Transition successfully performed.'
            );
        } catch (WorkflowNotFoundException $e) {
            throw new HttpException(
                500,
                sprintf('Workflow "%s" not found', $workflowItem->getWorkflowName())
            );
        } catch (UnknownTransitionException $e) {
            throw new HttpException(500, $e->getMessage());
        } catch (ForbiddenTransitionException $e) {
            $this->get('session')->getFlashBag()->add(
                'error',
                sprintf('Transition "%s" is not allowed', $transitionName)
            );
        }

        return $this->redirect(
            $this->generateUrl(
                'acme_demoworkflow_workflowitem_edit',
                array('id' => $workflowItem->getId())
            )
        );
        */
    }
}
