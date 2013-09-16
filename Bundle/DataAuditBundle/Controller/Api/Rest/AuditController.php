<?php

namespace Oro\Bundle\DataAuditBundle\Controller\Api\Rest;

use Symfony\Component\Validator\Constraints\True;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\Rest\Util\Codes;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Oro\Bundle\DataAuditBundle\Entity\Audit;
use Oro\Bundle\UserBundle\Annotation\Acl;

/**
 * @NamePrefix("oro_api_")
 *
 * @Acl(
 *      id="oro_dataaudit_api_rest",
 *      name="Rest API for data audit",
 *      description="Rest API for data audit",
 *      parent="oro_dataaudit"
 * )
 */
class AuditController extends FOSRestController implements ClassResourceInterface
{
    /**
     * Get list of audit logs
     *
     * @ApiDoc(
     *  description="Get list of all logged entities",
     *  resource=true
     * )
     *
     * @return Response
     *
     * @Acl(
     *      id="oro_dataaudit_api_rest_list",
     *      name="Get logged entities",
     *      description="Get list of all logged entities",
     *      parent="oro_dataaudit_api_rest"
     * )
     */
    public function cgetAction()
    {
        return $this->handleView(
            $this->view(
                $this->getDoctrine()->getRepository('OroDataAuditBundle:Audit')->findAll(),
                Codes::HTTP_OK
            )
        );
    }

    /**
     * Get page state
     *
     * @param int $id Page state id
     *
     * @ApiDoc(
     *  description="Get audit entity",
     *  resource=true,
     *  requirements={
     *      {"name"="id", "dataType"="integer"},
     *  }
     * )
     *
     * @return Response
     *
     * @Acl(
     *      id="oro_dataaudit_api_rest_get",
     *      name="Get audit entity",
     *      description="Get audit entity",
     *      parent="oro_dataaudit_api_rest"
     * )
     */
    public function getAction($id)
    {
        if (!$entity = $this->getEntity($id)) {
            return $this->handleView($this->view('', Codes::HTTP_NOT_FOUND));
        }

        return $this->handleView($this->view($entity, Codes::HTTP_OK));
    }

    /**
     * Remove audit entity
     *
     * @param int $d
     *
     * @ApiDoc(
     *  description="Remove audit entity",
     *  resource=true,
     *  requirements={
     *      {"name"="id", "dataType"="integer"},
     *  }
     * )
     *
     * @Acl(
     *      id="oro_dataaudit_api_rest_delete",
     *      name="Delete audit entity",
     *      description="Delete audit entity",
     *      parent="oro_dataaudit_api_rest"
     * )
     */
    public function deleteAction($id)
    {
        if (!$entity = $this->getEntity($id)) {
            return $this->handleView($this->view('', Codes::HTTP_NOT_FOUND));
        }

        $this->getManager()->remove($entity);
        $this->getManager()->flush();

        return $this->handleView($this->view('', Codes::HTTP_NO_CONTENT));
    }

    /**
     * Get entity Manager
     *
     * @return \Doctrine\Common\Persistence\ObjectManager
     */
    protected function getManager()
    {
        return $this->getDoctrine()->getManagerForClass('OroDataAuditBundle:Audit');
    }

    /**
     * Get entity by id
     *
     * @return Audit
     */
    protected function getEntity($id)
    {
        return $this->getDoctrine()->getRepository('OroDataAuditBundle:Audit')->findOneById((int) $id);
    }
}
