<?php

namespace Oro\Bundle\WorkflowBundle\Controller\Api\Rest;

use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
use Oro\Bundle\WorkflowBundle\Exception\UnknownAttributeException;

/**
 * @NamePrefix("oro_api_workflow_")
 */
class WorkflowController extends FOSRestController
{
    /**
     * Returns:
     * - HTTP_OK (200) response: array('workflowItemId' => workflowItemId)
     * - HTTP_FORBIDDEN (403) response: array('message' => errorMessageString)
     * - HTTP_NOT_FOUND (404) response: array('message' => errorMessageString)
     * - HTTP_INTERNAL_SERVER_ERROR (500) response: array('message' => errorMessageString)
     *
     * @Get("/start/{workflowName}/{entityClass}/{entityId}/{transitionName}")
     * @ApiDoc(description="Start workflow for entity from transition", resource=true)
     * @AclAncestor("oro_workflow")
     *
     * @param string $workflowName
     * @param string $entityClass
     * @param mixed $entityId
     * @param string $transitionName
     * @return Response
     */
    public function startAction($workflowName, $entityClass, $entityId, $transitionName)
    {
        try {
            /** @var EntityManager $em */
            $em = $this->getDoctrine()->getManagerForClass($entityClass);
            $entity = $em->getReference($entityClass, $entityId);

            /** @var WorkflowManager $workflowManager */
            $workflowManager = $this->get('oro_workflow.manager');
            $workflowItem = $workflowManager->startWorkflow($workflowName, $entity, $transitionName);
        } catch (WorkflowNotFoundException $e) {
            return $this->handleNotFoundException($e->getMessage());
        } catch (UnknownAttributeException $e) {
            return $this->handleNotFoundException($e->getMessage());
        } catch (UnknownTransitionException $e) {
            return $this->handleNotFoundException($e->getMessage());
        } catch (ForbiddenTransitionException $e) {
            return $this->handleForbiddenException($e->getMessage());
        } catch (\Exception $e) {
            return $this->handleDefaultException($e);
        }

        return $this->handleView(
            $this->view(array('workflowItemId' => $workflowItem->getId()), Codes::HTTP_OK)
        );
    }

    /**
     * Returns:
     * - HTTP_OK (200) response: true
     * - HTTP_FORBIDDEN (403) response: array('message' => errorMessageString)
     * - HTTP_NOT_FOUND (404) response: array('message' => errorMessageString)
     * - HTTP_INTERNAL_SERVER_ERROR (500) response: array('message' => errorMessageString)
     *
     * @Get("/transit/{workflowItemId}/{transitionName}", requirements={"workflowItemId"="\d+"})
     * @ParamConverter("workflowItem", options={"id"="workflowItemId"})
     * @ApiDoc(description="Perform transition for workflow item", resource=true)
     * @AclAncestor("oro_workflow")
     *
     * @param WorkflowItem $workflowItem
     * @param string $transitionName
     * @return Response
     */
    public function transitAction(WorkflowItem $workflowItem, $transitionName)
    {
        try {
            $this->get('oro_workflow.manager')->transit($workflowItem, $transitionName);
        } catch (WorkflowNotFoundException $e) {
            return $this->handleNotFoundException($e->getMessage());
        } catch (UnknownTransitionException $e) {
            return $this->handleNotFoundException($e->getMessage());
        } catch (ForbiddenTransitionException $e) {
            return $this->handleForbiddenException($e->getMessage());
        } catch (\Exception $e) {
            return $this->handleDefaultException($e);
        }

        return $this->handleView(
            $this->view(true, Codes::HTTP_OK)
        );
    }

    /**
     * @param string $message
     * @return array
     */
    protected function formatErrorResponse($message)
    {
        return array('message' => $message);
    }

    /**
     * @param string $message
     * @return Response
     */
    protected function handleNotFoundException($message)
    {
        return $this->handleView(
            $this->view(
                $this->formatErrorResponse($message),
                Codes::HTTP_NOT_FOUND
            )
        );
    }

    /**
     * @param string $message
     * @return Response
     */
    protected function handleForbiddenException($message)
    {
        return $this->handleView(
            $this->view(
                $this->formatErrorResponse($message),
                Codes::HTTP_FORBIDDEN
            )
        );
    }

    /**
     * @param \Exception $exception
     * @return Response
     */
    protected function handleDefaultException(\Exception $exception)
    {
        return $this->handleView(
            $this->view(
                $this->formatErrorResponse($exception->getMessage()),
                Codes::HTTP_INTERNAL_SERVER_ERROR
            )
        );
    }
}
