<?php

namespace Oro\Bundle\SoapBundle\Controller\Api\Rest;

use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\SoapBundle\Controller\Api\Rest\RestApiCrudInterface;
use Oro\Bundle\SoapBundle\Controller\Api\FormAwareInterface;
use Oro\Bundle\SoapBundle\Controller\Api\FormHandlerAwareInterface;
use Symfony\Component\HttpFoundation\Response;

use Doctrine\Common\Util\ClassUtils;

use FOS\Rest\Util\Codes;

abstract class RestController extends RestGetController implements
     FormAwareInterface,
     FormHandlerAwareInterface,
     RestApiCrudInterface
{
    /**
     * Edit entity
     *
     * @param  mixed    $id
     * @return Response
     */
    public function handleUpdateRequest($id)
    {
        $entity = $this->getManager()->find($id);
        if (!$entity) {
            return $this->handleView($this->view(null, Codes::HTTP_NOT_FOUND));
        }
        if (!$this->get('oro_security.security_facade')->isGranted('UPDATE', $entity)) {
            return $this->handleView($this->view(null, Codes::HTTP_FORBIDDEN));
        }

        if ($this->processForm($entity)) {
            $view = $this->view(null, Codes::HTTP_NO_CONTENT);
        } else {
            $view = $this->view($this->getForm(), Codes::HTTP_BAD_REQUEST);
        }

        return $this->handleView($view);
    }

    /**
     * Create new
     *
     * @return Response
     */
    public function handleCreateRequest()
    {
        $entity = $this->getManager()->createEntity();
        $isProcessed = $this->processForm($entity);

        if ($isProcessed) {
            $entityClass = ClassUtils::getRealClass(get_class($entity));
            $classMetadata = $this->getManager()->getObjectManager()->getClassMetadata($entityClass);
            $view = $this->view($classMetadata->getIdentifierValues($entity), Codes::HTTP_CREATED);
        } else {
            $view = $this->view($this->getForm(), Codes::HTTP_BAD_REQUEST);
        }

        return $this->handleView($view);
    }

    /**
     * Delete entity
     *
     * @param  mixed    $id
     * @return Response
     */
    public function handleDeleteRequest($id)
    {
        $entity = $this->getManager()->find($id);
        if (!$entity) {
            return $this->handleView($this->view(null, Codes::HTTP_NOT_FOUND));
        }

        if (!$this->get('oro_security.security_facade')->isGranted('DELETE', $entity)) {
            return $this->handleView($this->view(null, Codes::HTTP_FORBIDDEN));
        }

        $em = $this->getManager()->getObjectManager();
        $this->handleDelete($entity, $em);
        $em->flush();

        return $this->handleView($this->view(null, Codes::HTTP_NO_CONTENT));
    }

    /**
     * Process form.
     *
     * @param  mixed $entity
     * @return bool
     */
    protected function processForm($entity)
    {
        return $this->getFormHandler()->process($entity);
    }

    /**
     * Handle delete entity object.
     *
     * @param object        $entity
     * @param ObjectManager $em
     */
    protected function handleDelete($entity, ObjectManager $em)
    {
        $em->remove($entity);
    }
}
