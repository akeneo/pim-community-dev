<?php

namespace Oro\Bundle\EmailBundle\Controller\Api\Rest;

use FOS\Rest\Util\Codes;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\RouteResource;

use Oro\Bundle\EmailBundle\Entity\Repository\EmailTemplateRepository;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;

use Oro\Bundle\UserBundle\Annotation\AclAncestor;
use Oro\Bundle\UserBundle\Annotation\Acl;
use Oro\Bundle\SoapBundle\Form\Handler\ApiFormHandler;
use Oro\Bundle\SoapBundle\Entity\Manager\ApiEntityManager;
use Oro\Bundle\SoapBundle\Controller\Api\Rest\RestController;

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
        return $this->handleDeleteRequest($id);
    }

    /**
     * REST GET templates by entity name
     *
     * @param string $entityName
     *
     * @ApiDoc(
     *  description="Get templates by entity name",
     *  resource=true
     * )
     * @AclAncestor("oro_email_emailtemplate")
     * @return Response
     */
    public function getAction($entityName = null)
    {
        if (!$entityName) {
            return $this->handleView(
                $this->view(null, Codes::HTTP_NOT_FOUND)
            );
        }

        /** @var $emailTemplateRepository EmailTemplateRepository */
        $emailTemplateRepository = $this->getDoctrine()->getRepository('OroEmailBundle:EmailTemplate');
        $templates = $emailTemplateRepository->getTemplateByEntityName($entityName);

        return $this->handleView(
            $this->view($templates, Codes::HTTP_OK)
        );
    }

    /**
     * Get entity Manager
     *
     * @return ApiEntityManager
     */
    public function getManager()
    {
        return $this->get('oro_notification.email_notification.manager.api');
    }

    /**
     * @return FormInterface
     */
    public function getForm()
    {
        return $this->get('oro_notification.form.type.email_notification.api');
    }

    /**
     * @return ApiFormHandler
     */
    public function getFormHandler()
    {
        return $this->get('oro_notification.form.handler.email_notification.api');
    }
}
