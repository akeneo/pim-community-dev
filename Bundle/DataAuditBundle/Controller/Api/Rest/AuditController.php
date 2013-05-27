<?php

namespace Oro\Bundle\DataAuditBundle\Controller\Api\Rest;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\Rest\Util\Codes;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Oro\Bundle\DataAuditBundle\Entity\Audit;
use Symfony\Component\Validator\Constraints\True;

/**
 * @NamePrefix("oro_api_")
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
