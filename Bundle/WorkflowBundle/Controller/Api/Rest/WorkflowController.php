<?php

namespace Oro\Bundle\WorkflowBundle\Controller\Api\Rest;

use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
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
     * - HTTP_BAD_REQUEST (400) response: array('message' => errorMessageString)
     * - HTTP_FORBIDDEN (403) response: array('message' => errorMessageString)
     * - HTTP_NOT_FOUND (404) response: array('message' => errorMessageString)
     * - HTTP_INTERNAL_SERVER_ERROR (500) response: array('message' => errorMessageString)
     *
     * @Get("/start/{workflowName}/{entityClass}/{entityId}/{transitionName}")
     * @ApiDoc(description="Start workflow for entity from transition", resource=true)
     * @AclAncestor("oro_workflow")
     *
     * @param string $workflowName
     * @param string $transitionName
     * @return Response
     */
    public function startAction($workflowName, $transitionName)
    {
        try {
            /** @var WorkflowManager $workflowManager */
            $workflowManager = $this->get('oro_workflow.manager');

            $entity = null;
            $entityClass = $this->getRequest()->get('entityClass');
            $entityId = $this->getRequest()->get('entityId');

            if ($entityClass && $entityId) {
                $entity = $this->getEntityReference($entityClass, $entityId);
            }

            $workflowItem = $workflowManager->startWorkflow($workflowName, $entity, $transitionName);
        } catch (HttpException $e) {
            return $this->handleError($e->getMessage(), $e->getStatusCode());
        } catch (WorkflowNotFoundException $e) {
            return $this->handleError($e->getMessage(), Codes::HTTP_NOT_FOUND);
        } catch (UnknownAttributeException $e) {
            return $this->handleError($e->getMessage(), Codes::HTTP_BAD_REQUEST);
        } catch (UnknownTransitionException $e) {
            return $this->handleError($e->getMessage(), Codes::HTTP_BAD_REQUEST);
        } catch (ForbiddenTransitionException $e) {
            return $this->handleError($e->getMessage(), Codes::HTTP_FORBIDDEN);
        } catch (\Exception $e) {
            return $this->handleError($e->getMessage(), Codes::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->handleView(
            $this->view(array('workflowItemId' => $workflowItem->getId()), Codes::HTTP_OK)
        );
    }

    /**
     * Gets reference to entity
     *
     * @param string $entityClass $entityId
     * @param mixed $entityId
     * @return object
     * @throws BadRequestHttpException If entity class is invalid
     */
    protected function getEntityReference($entityClass, $entityId)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManagerForClass($entityClass);
        if (!$em) {
            throw new BadRequestHttpException(
                sprintf('Class "%s" is not manageable entity', $entityClass)
            );
        }

        return $em->getReference($entityClass, $entityId);
    }

    /**
     * Returns:
     * - HTTP_OK (200) response: true
     * - HTTP_BAD_REQUEST (400) response: array('message' => errorMessageString)
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
            return $this->handleError($e->getMessage(), Codes::HTTP_NOT_FOUND);
        } catch (UnknownTransitionException $e) {
            return $this->handleError($e->getMessage(), Codes::HTTP_BAD_REQUEST);
        } catch (ForbiddenTransitionException $e) {
            return $this->handleError($e->getMessage(), Codes::HTTP_FORBIDDEN);
        } catch (\Exception $e) {
            return $this->handleError($e->getMessage(), Codes::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->handleView(
            $this->view(true, Codes::HTTP_OK)
        );
    }

    /**
     * @param string $message
     * @param int $code
     * @return Response
     */
    protected function handleError($message, $code)
    {
        return $this->handleView(
            $this->view(
                $this->formatErrorResponse($message),
                $code
            )
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
}
