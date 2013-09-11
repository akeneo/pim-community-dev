<?php

namespace Oro\Bundle\TagBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\TagBundle\Entity\Tag;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\GridBundle\Datagrid\Datagrid;
use Oro\Bundle\TagBundle\Datagrid\TagDatagridManager;
use Oro\Bundle\TagBundle\Datagrid\ResultsDatagridManager;

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
     *      id="oro_tag_list",
     *      type="entity",
     *      class="OroTagBundle:Tag",
     *      permission="VIEW"
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
     * @Route("/create", name="oro_tag_create")
     * @Acl(
     *      id="oro_tag_create",
     *      type="entity",
     *      class="OroTagBundle:Tag",
     *      permission="CREATE"
     * )
     * @Template("OroTagBundle:Tag:update.html.twig")
     */
    public function createAction()
    {
        return $this->updateAction(new Tag());
    }

    /**
     * @Route("/update/{id}", name="oro_tag_update", requirements={"id"="\d+"}, defaults={"id"=0})
     * @Acl(
     *      id="oro_tag_update",
     *      type="entity",
     *      class="OroTagBundle:Tag",
     *      permission="EDIT"
     * )
     * @Template
     */
    public function updateAction(Tag $entity)
    {
        if ($this->get('oro_tag.form.handler.tag')->process($entity)) {
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
     * @Route("/search/{id}", name="oro_tag_search", requirements={"id"="\d+"}, defaults={"id"=0})
     * @Template
     */
    public function searchAction(Tag $entity, Request $request)
    {
        $from = $request->get('from');
        $datagrid = $this->getSearchResultsDatagrid($from, $entity);

        /** @var \Oro\Bundle\TagBundle\Provider\SearchProvider $provider */
        $provider = $this->get('oro_tag.provider.search_provider');

        return array(
            'tag'            => $entity,
            'from'           => $from,
            'groupedResults' => $provider->getGroupedResults($entity->getId()),
            'datagrid'       => $datagrid->createView()
        );
    }

    /**
     * Return search results in json for datagrid
     *
     * @Route("/ajax/{id}", name="oro_tag_search_ajax", requirements={"id"="\d+"}, defaults={"id"=0})
     */
    public function searchResultsAjaxAction(Tag $entity, Request $request)
    {
        $from   = $request->get('from');
        $datagrid = $this->getSearchResultsDatagrid($from, $entity);

        return $this->get('oro_grid.renderer')->renderResultsJsonResponse($datagrid->createView());
    }

    /**
     * @param  string   $from
     * @param  Tag      $tag
     * @return Datagrid
     */
    protected function getSearchResultsDatagrid($from, Tag $tag)
    {
        /** @var $datagridManager ResultsDatagridManager */
        $datagridManager = $this->get('oro_tag.datagrid_results.datagrid_manager');

        $datagridManager->setSearchEntity($from);
        $datagridManager->setTag($tag);
        $datagridManager->getRouteGenerator()->setRouteParameters(
            array(
                'from'   => $from,
                'id'     => $tag->getId(),
            )
        );

        return $datagridManager->getDatagrid();
    }
}
