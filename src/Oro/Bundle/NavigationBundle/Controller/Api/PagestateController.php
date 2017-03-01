<?php

namespace Oro\Bundle\NavigationBundle\Controller\Api;

use Doctrine\Common\Persistence\ObjectManager;
use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Oro\Bundle\NavigationBundle\Entity\PageState;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @NamePrefix("oro_api_")
 */
class PagestateController extends FOSRestController implements ClassResourceInterface
{
    /**
     * Get list of user's page states
     */
    public function cgetAction()
    {
        return $this->handleView(
            $this->view(
                $this->getDoctrine()->getRepository('OroNavigationBundle:PageState')->findBy(
                    ['user' => $this->getUser()]
                ),
                Response::HTTP_OK
            )
        );
    }

    /**
     * Get page state
     *
     * @param int $id Page state id
     *
     * @return Response
     */
    public function getAction($id)
    {
        if (!$entity = $this->getEntity($id)) {
            return $this->handleView($this->view('', Response::HTTP_NOT_FOUND));
        }

        return $this->handleView($this->view($entity, Response::HTTP_OK));
    }

    /**
     * Create new page state
     */
    public function postAction()
    {
        $entity = new PageState();

        $view = $this->get('oro_navigation.form.handler.pagestate')->process($entity)
            ? $this->view($this->getState($entity), Response::HTTP_CREATED)
            : $this->view($this->get('oro_navigation.form.pagestate'), Response::HTTP_BAD_REQUEST);

        return $this->handleView($view);
    }

    /**
     * Update existing page state
     *
     * @param int $id Page state id
     *
     * @return Response
     */
    public function putAction($id)
    {
        if (!$entity = $this->getEntity($id)) {
            return $this->handleView($this->view('', Response::HTTP_NOT_FOUND));
        }

        $view = $this->get('oro_navigation.form.handler.pagestate')->process($entity)
            ? $this->view('', Response::HTTP_NO_CONTENT)
            : $this->view($this->get('oro_navigation.form.pagestate'), Response::HTTP_BAD_REQUEST);

        return $this->handleView($view);
    }

    /**
     * Remove page state
     *
     * @param int $id
     *
     * @return Response
     */
    public function deleteAction($id)
    {
        if (!$entity = $this->getEntity($id)) {
            return $this->handleView($this->view('', Response::HTTP_NOT_FOUND));
        }

        $this->getManager()->remove($entity);
        $this->getManager()->flush();

        return $this->handleView($this->view('', Response::HTTP_NO_CONTENT));
    }

    /**
     * Check if page id already exists
     *
     * @QueryParam(name="pageId", nullable=false, description="Unique page id")
     * @param Request $request
     *
     * @return Response
     */
    public function getCheckidAction(Request $request)
    {
        $entity = $this
            ->getDoctrine()
            ->getRepository('OroNavigationBundle:PageState')
            ->findOneBy([
                'pageHash' => PageState::generateHash($request->get('pageId'))
            ]);

        return $this->handleView($this->view($this->getState($entity), Response::HTTP_OK));
    }

    /**
     * Get entity Manager
     *
     * @return ObjectManager
     */
    protected function getManager()
    {
        return $this->getDoctrine()->getManagerForClass('OroNavigationBundle:PageState');
    }

    /**
     * Get entity by id
     *
     * @param string|int $id
     *
     * @return PageState
     */
    protected function getEntity($id)
    {
        return $this->getDoctrine()->getRepository('OroNavigationBundle:PageState')->findOneBy(['id' => (int)$id]);
    }

    /**
     * Get State for Backbone model
     *
     * @param  PageState $entity
     *
     * @return array
     */
    protected function getState(PageState $entity = null)
    {
        return [
            'id'        => $entity ? $entity->getId() : null,
            'pagestate' => [
                'data'   => $entity ? $entity->getData() : '',
                'pageId' => $entity ? $entity->getPageId() : ''
            ]
        ];
    }
}
