<?php

namespace Oro\Bundle\EmailBundle\Controller\Api\Rest;

use FOS\Rest\Util\Codes;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\Annotations\Get as GetRoute;
use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\RouteResource;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;

use Oro\Bundle\UserBundle\Annotation\Acl;
use Oro\Bundle\UserBundle\Annotation\AclAncestor;
use Oro\Bundle\SoapBundle\Form\Handler\ApiFormHandler;
use Oro\Bundle\EmailBundle\Provider\VariablesProvider;
use Oro\Bundle\SoapBundle\Entity\Manager\ApiEntityManager;
use Oro\Bundle\SoapBundle\Controller\Api\Rest\RestController;
use Oro\Bundle\EmailBundle\Entity\Repository\EmailTemplateRepository;

/**
 * @RouteResource("emailtemplate")
 * @NamePrefix("oro_api_")
 */
class EmailTemplateController extends RestController
{
    /**
     * REST DELETE
     *
     * @param int $id
     *
     * @ApiDoc(
     *      description="Delete email template",
     *      resource=true
     * )
     * @Acl(
     *      id="oro_email_emailtemplate_remove",
     *      name="Delete email template",
     *      description="Delete email template",
     *      parent="oro_email_emailtemplate"
     * )
     * @return Response
     */
    public function deleteAction($id)
    {
        $entity = $this->getManager()->find($id);
        if (!$entity) {
            return $this->handleView($this->view(null, Codes::HTTP_NOT_FOUND));
        }

        /**
         * Deny to remove system templates
         *
         * @TODO hide icon in datagrid when it'll be possible
         */
        if ($entity->getIsSystem()) {
            return $this->handleView($this->view(null, Codes::HTTP_FORBIDDEN));
        }

        $em = $this->getManager()->getObjectManager();
        $em->remove($entity);
        $em->flush();

        return $this->handleView($this->view(null, Codes::HTTP_NO_CONTENT));
    }

    /**
     * REST GET templates by entity name
     *
     * @param string $entityName
     *
     * @ApiDoc(
     *     description="Get templates by entity name",
     *     resource=true
     * )
     * @AclAncestor("oro_email_emailtemplate_index")
     * @return Response
     */
    public function getAction($entityName = null)
    {
        if (!$entityName) {
            return $this->handleView(
                $this->view(null, Codes::HTTP_NOT_FOUND)
            );
        }
        $entityName = str_replace('_', '\\', $entityName);

        /** @var $emailTemplateRepository EmailTemplateRepository */
        $emailTemplateRepository = $this->getDoctrine()->getRepository('OroEmailBundle:EmailTemplate');
        $templates = $emailTemplateRepository->getTemplateByEntityName($entityName);

        return $this->handleView(
            $this->view($templates, Codes::HTTP_OK)
        );
    }

    /**
     * REST GET available variables by entity name
     *
     * @param string $entityName
     *
     * @ApiDoc(
     *     description="Get available variables by entity name",
     *     resource=true
     * )
     * @AclAncestor("oro_email_emailtemplate_update")
     * @GetRoute(requirements={"entityName"="(.*)"})
     * @return Response
     */
    public function getAvailableVariablesAction($entityName = null)
    {
        $entityName = str_replace('_', '\\', $entityName);

        /** @var VariablesProvider $provider */
        $provider = $this->get('oro_email.provider.variable_provider');
        $allowedData = $provider->getTemplateVariables($entityName);

        return $this->handleView(
            $this->view($allowedData, Codes::HTTP_OK)
        );
    }

    /**
     * Get entity Manager
     *
     * @return ApiEntityManager
     */
    public function getManager()
    {
        return $this->get('oro_email.manager.emailtemplate.api');
    }

    /**
     * @return FormInterface
     */
    public function getForm()
    {
        return $this->get('oro_email.form.type.emailtemplate.api');
    }

    /**
     * @return ApiFormHandler
     */
    public function getFormHandler()
    {
        return $this->get('oro_email.form.handler.emailtemplate.api');
    }
}
