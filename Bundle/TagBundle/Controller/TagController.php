<?php

namespace Oro\Bundle\TagBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\TagBundle\Entity\Tag;
use Oro\Bundle\UserBundle\Annotation\Acl;
use Oro\Bundle\UserBundle\Annotation\AclAncestor;
use Oro\Bundle\TagBundle\Datagrid\TagDatagridManager;

/**
 * @Acl(
 *      id="oro_tag",
 *      name="Tags",
 *      description="Tags",
 *      parent="root"
 * )
 */
class TagController extends Controller
{
    /**
     * @Route(
     *      "/{_format}",
     *      name="oro_tag_index",
     *      requirements={"_format"="html|json"},
     *      defaults={"_format" = "html"}
     * )
     * @Acl(
     *      id="oro_tag_grid_and_edit",
     *      name="View and edit tags",
     *      description="User can see grid of tags and edit tag",
     *      parent="oro_tag"
     * )
     * @Template
     */
    public function indexAction()
    {
        /** @var $gridManager TagDatagridManager */
        $gridManager = $this->get('oro_tag.datagrid_manager');
        $datagridView = $gridManager->getDatagrid()->createView();

        if ('json' == $this->getRequest()->getRequestFormat()) {
            return $this->get('oro_grid.renderer')->renderResultsJsonResponse($datagridView);
        }

        return array('datagrid' => $datagridView);
    }

    /**
     * @Route("/update/{id}", name="oro_tag_update", requirements={"id"="\d+"}, defaults={"id"=0})
     * @AclAncestor("oro_tag_grid_and_edit")
     * @Template
     */
    public function updateAction(Tag $entity)
    {
        if ($this->get('oro_tag.tag.form.handler.tag')->process($entity)) {
            $this->get('session')->getFlashBag()->add(
                'success',
                $this->get('translator')->trans('oro.tag.controller.tag.saved.message')
            );

            return $this->redirect($this->generateUrl('oro_tag_index'));
        }

        return array(
            'form' => $this->get('oro_tag.form.tag')->createView(),
        );
    }

    /**
     * @Route(
     *      "/{_format}",
     *      name="oro_tag_view",
     *      requirements={"_format"="html|json"},
     *      defaults={"_format" = "html"}
     * )
     * @AclAncestor("oro_tag_grid_and_edit")
     * @Template
     */
    public function viewAction()
    {
        return array();
    }
}
