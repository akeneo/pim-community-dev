<?php

namespace Oro\Bundle\WorkflowBundle\Controller\Api\Rest;

use Doctrine\ORM\EntityManager;
use Oro\Bundle\WorkflowBundle\Model\WorkflowData;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\Rest\Util\Codes;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\WorkflowBundle\Exception\WorkflowNotFoundException;
use Oro\Bundle\WorkflowBundle\Model\Workflow;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Bundle\WorkflowBundle\Model\WorkflowManager;
use Oro\Bundle\WorkflowBundle\Exception\InvalidTransitionException;
use Oro\Bundle\WorkflowBundle\Exception\ForbiddenTransitionException;
use Oro\Bundle\WorkflowBundle\Exception\UnknownAttributeException;

/**
 * @Rest\NamePrefix("oro_api_workflow_")
 */
class WorkflowController extends FOSRestController
{
    /**
     * Returns:
     * - HTTP_OK (200) response: array('workflowItem' => array('id' => int, 'result' => array(...), ...))
     * - HTTP_BAD_REQUEST (400) response: array('message' => errorMessageString)
     * - HTTP_FORBIDDEN (403) response: array('message' => errorMessageString)
     * - HTTP_NOT_FOUND (404) response: array('message' => errorMessageString)
     * - HTTP_INTERNAL_SERVER_ERROR (500) response: array('message' => errorMessageString)
     *
     * @Rest\Get("/start/{workflowName}/{transitionName}", defaults={"_format"="json"})
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

            $data = $this->getRequest()->get('data');
            $dataArray = array();
            if ($data) {
                $serializer = $this->get('oro_workflow.serializer.data.serializer');
                $serializer->setWorkflowName($workflowName);
                /** @var WorkflowData $data */
                $data = $serializer->deserialize(
                    $data,
                    'Oro\Bundle\WorkflowBundle\Model\WorkflowData',
                    'json'
                );
                $dataArray = $data->getValues();
            }

            if ($entityClass && $entityId) {
                $entity = $this->getEntityReference($entityClass, $entityId);
            }

            $workflowItem = $workflowManager->startWorkflow($workflowName, $entity, $transitionName, $dataArray);
        } catch (HttpException $e) {
            return $this->handleError($e->getMessage(), $e->getStatusCode());
        } catch (WorkflowNotFoundException $e) {
            return $this->handleError($e->getMessage(), Codes::HTTP_NOT_FOUND);
        } catch (UnknownAttributeException $e) {
            return $this->handleError($e->getMessage(), Codes::HTTP_BAD_REQUEST);
        } catch (InvalidTransitionException $e) {
            return $this->handleError($e->getMessage(), Codes::HTTP_BAD_REQUEST);
        } catch (ForbiddenTransitionException $e) {
            return $this->handleError($e->getMessage(), Codes::HTTP_FORBIDDEN);
        } catch (\Exception $e) {
            return $this->handleError($e->getMessage(), Codes::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->handleView(
            $this->view(
                array(
                    'workflowItem' => $workflowItem
                ),
                Codes::HTTP_OK
            )
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
     * - HTTP_OK (200) response: array('workflowItem' => array('id' => int, 'result' => array(...), ...))
     * - HTTP_BAD_REQUEST (400) response: array('message' => errorMessageString)
     * - HTTP_FORBIDDEN (403) response: array('message' => errorMessageString)
     * - HTTP_NOT_FOUND (404) response: array('message' => errorMessageString)
     * - HTTP_INTERNAL_SERVER_ERROR (500) response: array('message' => errorMessageString)
     *
     * @Rest\Get(
     *      "/{workflowItemId}/transit/{transitionName}",
     *      requirements={"workflowItemId"="\d+"},
     *      defaults={"_format"="json"}
     * )
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
        $this->get('oro_workflow.http.workflow_item_validator')->validate($workflowItem);

        try {
            $this->get('oro_workflow.manager')->transit($workflowItem, $transitionName);
        } catch (WorkflowNotFoundException $e) {
            return $this->handleError($e->getMessage(), Codes::HTTP_NOT_FOUND);
        } catch (InvalidTransitionException $e) {
            return $this->handleError($e->getMessage(), Codes::HTTP_BAD_REQUEST);
        } catch (ForbiddenTransitionException $e) {
            return $this->handleError($e->getMessage(), Codes::HTTP_FORBIDDEN);
        } catch (\Exception $e) {
            return $this->handleError($e->getMessage(), Codes::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->handleView(
            $this->view(
                array(
                    'workflowItem' => $workflowItem
                ),
                Codes::HTTP_OK
            )
        );
    }

    /**
     * Returns
     * - HTTP_OK (200) response: array('workflowItem' => array('id' => int, 'result' => array(...), ...))
     *
     * @Rest\Get("/{workflowItemId}", requirements={"workflowItemId"="\d+"}, defaults={"_format"="json"})
     * @ParamConverter("workflowItem", options={"id"="workflowItemId"})
     * @ApiDoc(description="Get workflow item", resource=true)
     * @AclAncestor("oro_workflow")
     *
     * @param WorkflowItem $workflowItem
     * @return Response
     */
    public function getAction(WorkflowItem $workflowItem)
    {
        $this->get('oro_workflow.http.workflow_item_validator')->validate($workflowItem);

        return $this->handleView(
            $this->view(
                array(
                    'workflowItem' => $workflowItem
                ),
                Codes::HTTP_OK
            )
        );
    }

    /**
     * Delete workflow item
     *
     * Returns
     * - HTTP_NO_CONTENT (204)
     *
     * @Rest\Delete("/{workflowItemId}", requirements={"workflowItemId"="\d+"}, defaults={"_format"="json"})
     * @ParamConverter("workflowItem", options={"id"="workflowItemId"})
     * @ApiDoc(description="Delete workflow item", resource=true)
     * @AclAncestor("oro_workflow")
     *
     * @param WorkflowItem $workflowItem
     * @return Response
     */
    public function deleteAction(WorkflowItem $workflowItem)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($workflowItem);
        $em->flush();
        return $this->handleView($this->view(null, Codes::HTTP_NO_CONTENT));
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
