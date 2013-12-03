<?php

namespace Oro\Bundle\EmailBundle\Controller\Api\Rest;

use FOS\Rest\Util\Codes;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Controller\Annotations\QueryParam;

use Symfony\Component\HttpFoundation\Response;

use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SoapBundle\Controller\Api\Rest\RestGetController;
use Oro\Bundle\EmailBundle\Entity\Manager\EmailApiEntityManager;
use Oro\Bundle\EmailBundle\Entity\Email;

/**
 * @RouteResource("email")
 * @NamePrefix("oro_api_")
 */
class EmailController extends RestGetController
{
    /**
     * REST GET list
     *
     * @QueryParam(
     *      name="page",
     *      requirements="\d+",
     *      nullable=true,
     *      description="Page number, starting from 1. Defaults to 1."
     * )
     * @QueryParam(
     *      name="limit",
     *      requirements="\d+",
     *      nullable=true,
     *      description="Number of items per page. Defaults to 10."
     * )
     * @ApiDoc(
     *      description="Get all emails",
     *      resource=true
     * )
     * @AclAncestor("oro_email_view")
     * @return Response
     */
    public function cgetAction()
    {
        $page = (int)$this->getRequest()->get('page', 1);
        $limit = (int)$this->getRequest()->get('limit', self::ITEMS_PER_PAGE);

        return $this->handleGetListRequest($page, $limit);
    }

    /**
     * REST GET item
     *
     * @param string $id
     *
     * @ApiDoc(
     *      description="Get email",
     *      resource=true
     * )
     * @AclAncestor("oro_email_view")
     * @return Response
     */
    public function getAction($id)
    {
        return $this->handleGetRequest($id);
    }

    /**
     * Get entity manager
     *
     * @return EmailApiEntityManager
     */
    public function getManager()
    {
        return $this->container->get('oro_email.manager.email.api');
    }
}
