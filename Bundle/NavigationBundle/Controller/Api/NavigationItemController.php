<?php

namespace Oro\Bundle\NavigationBundle\Controller\Api;

use FOS\Rest\Util\Codes;
use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Controller\FOSRestController;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Oro\Bundle\NavigationBundle\Entity\Builder\ItemFactory;
use Oro\Bundle\NavigationBundle\Entity\Repository\NavigationRepositoryInterface;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Pim\Bundle\UserBundle\Entity\UserInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * @RouteResource("navigationitems")
 * @NamePrefix("oro_api_")
 */
class NavigationItemController extends FOSRestController
{
    /**
     * REST GET list
     *
     * @param string $type
     *
     * @ApiDoc(
     *  description="Get all Navigation items for user",
     *  resource=true
     * )
     * @return Response
     */
    public function getAction($type)
    {
        /** @var $entity \Oro\Bundle\NavigationBundle\Entity\NavigationItemInterface */
        $entity = $this->getFactory()->createItem($type, array());

        /** @var $repo NavigationRepositoryInterface */
        $repo = $this->getDoctrine()->getRepository(get_class($entity));
        $items = $repo->getNavigationItems($this->getUser(), $type);

        return $this->handleView(
            $this->view($items, is_array($items) ? Codes::HTTP_OK : Codes::HTTP_NOT_FOUND)
        );
    }

    /**
     * REST POST
     *
     * @param string $type
     *
     * @ApiDoc(
     *  description="Add Navigation item",
     *  resource=true
     * )
     * @return Response
     */
    public function postAction($type)
    {
        $params = $this->getRequest()->request->all();

        if (empty($params) || empty($params['type'])) {
            return $this->handleView(
                $this->view(
                    array('message' => 'Wrong JSON inside POST body'),
                    Codes::HTTP_BAD_REQUEST
                )
            );
        }

        $params['user'] = $this->getUser();
        $params['url']  = $this->getStateUrl($params['url']);

        /** @var $entity \Oro\Bundle\NavigationBundle\Entity\NavigationItemInterface */
        $entity = $this->getFactory()->createItem($type, $params);

        if (!$entity) {
            return $this->handleView($this->view(array(), Codes::HTTP_NOT_FOUND));
        }

        $em = $this->getManager();

        $em->persist($entity);
        $em->flush();

        return $this->handleView(
            $this->view(array('id' => $entity->getId(), 'url' => $params['url']), Codes::HTTP_CREATED)
        );
    }

    /**
     * REST PUT
     *
     * @param string $type
     * @param int    $itemId Navigation item id
     *
     * @ApiDoc(
     *  description="Update Navigation item",
     *  resource=true
     * )
     * @return Response
     */
    public function putIdAction($type, $itemId)
    {
        $params = $this->getRequest()->request->all();

        if (empty($params)) {
            return $this->handleView(
                $this->view(
                    array('message' => 'Wrong JSON inside POST body'),
                    Codes::HTTP_BAD_REQUEST
                )
            );
        }

        /** @var $entity \Oro\Bundle\NavigationBundle\Entity\NavigationItemInterface */
        $entity = $this->getFactory()->findItem($type, (int) $itemId);

        if (!$entity) {
            return $this->handleView($this->view(array(), Codes::HTTP_NOT_FOUND));
        }

        if (!$this->validatePermissions($entity->getUser())) {
            return $this->handleView($this->view(array(), Codes::HTTP_FORBIDDEN));
        }

        if (isset($params['url']) && !empty($params['url'])) {
            $params['url'] = $this->getStateUrl($params['url']);
        }

        $entity->setValues($params);

        $em = $this->getManager();

        $em->persist($entity);
        $em->flush();

        return $this->handleView($this->view(array(), Codes::HTTP_OK));
    }

    /**
     * REST DELETE
     *
     * @param string $type
     * @param int    $itemId
     *
     * @ApiDoc(
     *  description="Remove Navigation item",
     *  resource=true
     * )
     * @return Response
     */
    public function deleteIdAction($type, $itemId)
    {
        /** @var $entity \Oro\Bundle\NavigationBundle\Entity\NavigationItemInterface */
        $entity = $this->getFactory()->findItem($type, (int) $itemId);
        if (!$entity) {
            return $this->handleView($this->view(array(), Codes::HTTP_NOT_FOUND));
        }
        if (!$this->validatePermissions($entity->getUser())) {
            return $this->handleView($this->view(array(), Codes::HTTP_FORBIDDEN));
        }

        $em = $this->getManager();
        $em->remove($entity);
        $em->flush();

        return $this->handleView($this->view(array(), Codes::HTTP_NO_CONTENT));
    }

    /**
     * Validate permissions on pinbar
     *
     * @param  UserInterface $user
     * @return bool
     */
    protected function validatePermissions(UserInterface $user)
    {
        return $user->getId() == ($this->getUser() ? $this->getUser()->getId() : 0);
    }

    /**
     * Get entity Manager
     *
     * @return \Doctrine\Common\Persistence\ObjectManager
     */
    protected function getManager()
    {
        return $this->getDoctrine()->getManagerForClass('OroNavigationBundle:PinbarTab');
    }

    /**
     * Get entity factory
     *
     * @return ItemFactory
     */
    protected function getFactory()
    {
        return $this->get('oro_navigation.item.factory');
    }

    /**
     * Check if navigation item has corresponding page state and return modified URL
     *
     * @param  string $url Original URL
     * @return string Modified URL
     */
    protected function getStateUrl($url)
    {
        $state = $this
            ->getDoctrine()
            ->getRepository('OroNavigationBundle:PageState')
            ->findOneByPageId(base64_encode($url));

        return is_null($state)
            ? $url
            : $url . (strpos($url, '?') ? '&restore=1' : '?restore=1');
    }
}
