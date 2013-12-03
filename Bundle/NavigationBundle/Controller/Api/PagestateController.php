<?php

namespace Oro\Bundle\NavigationBundle\Controller\Api;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\Rest\Util\Codes;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Oro\Bundle\NavigationBundle\Entity\PageState;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

use Symfony\Component\Validator\Constraints\True;

/**
 * @NamePrefix("oro_api_")
 */
class PagestateController extends FOSRestController implements ClassResourceInterface
{
    /**
     * Get list of user's page states
     *
     * @ApiDoc(
     *  description="Get list of user's page states",
     *  resource=true
     * )
     */
    public function cgetAction()
    {
        return $this->handleView(
            $this->view(
                $this->getDoctrine()->getRepository('OroNavigationBundle:PageState')->findBy(
                    array('user' => $this->getUser())
                ),
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
     *  description="Get page state",
     *  resource=true,
     *  requirements={
     *      {"name"="id", "dataType"="integer"},
     *  }
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
     * Create new page state
     *
     * @ApiDoc(
     *  description="Create new page state",
     *  resource=true
     * )
     */
    public function postAction()
    {
        $entity = new PageState();

        $view = $this->get('oro_navigation.form.handler.pagestate')->process($entity)
            ? $this->view($this->getState($entity), Codes::HTTP_CREATED)
            : $this->view($this->get('oro_navigation.form.pagestate'), Codes::HTTP_BAD_REQUEST);

        return $this->handleView($view);
    }

    /**
     * Update existing page state
     *
     * @param int $id Page state id
     *
     * @ApiDoc(
     *  description="Update existing page state",
     *  resource=true,
     *  requirements={
     *      {"name"="id", "dataType"="integer"},
     *  }
     * )
     */
    public function putAction($id)
    {
        if (!$entity = $this->getEntity($id)) {
            return $this->handleView($this->view('', Codes::HTTP_NOT_FOUND));
        }

        $view = $this->get('oro_navigation.form.handler.pagestate')->process($entity)
            ? $this->view('', Codes::HTTP_NO_CONTENT)
            : $this->view($this->get('oro_navigation.form.pagestate'), Codes::HTTP_BAD_REQUEST);

        return $this->handleView($view);
    }

    /**
     * Remove page state
     *
     * @param int $d
     *
     * @ApiDoc(
     *  description="Remove page state",
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
     * Check if page id already exists
     *
     * @QueryParam(name="pageId", nullable=false, description="Unique page id")
     *
     * @ApiDoc(
     *  description="Check if page id already exists",
     *  resource=true
     * )
     */
    public function getCheckidAction()
    {
        $entity = $this
            ->getDoctrine()
            ->getRepository('OroNavigationBundle:PageState')
            ->findOneByPageHash(PageState::generateHash($this->getRequest()->get('pageId')));

        return $this->handleView($this->view($this->getState($entity), Codes::HTTP_OK));
    }

    /**
     * Get entity Manager
     *
     * @return \Doctrine\Common\Persistence\ObjectManager
     */
    protected function getManager()
    {
        return $this->getDoctrine()->getManagerForClass('OroNavigationBundle:PageState');
    }

    /**
     * Get entity by id
     *
     * @return PageState
     */
    protected function getEntity($id)
    {
        return $this->getDoctrine()->getRepository('OroNavigationBundle:PageState')->findOneById((int) $id);
    }

    /**
     * Get State for Backbone model
     *
     * @param  PageState $entity
     * @return array
     */
    protected function getState(PageState $entity = null)
    {
        return array(
            'id' => $entity ? $entity->getId() : null,
            'pagestate' => array(
                'data'   => $entity ? $entity->getData() : '',
                'pageId' => $entity ? $entity->getPageId() : ''
            )
        );
    }
}
