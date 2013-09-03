<?php

namespace Pim\Bundle\CatalogBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Oro\Bundle\UserBundle\Annotation\Acl;
use Pim\Bundle\CatalogBundle\Entity\Channel;

/**
 * Channel controller
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @Acl(
 *      id="pim_catalog_channel",
 *      name="Channel manipulation",
 *      description="Channel manipulation",
 *      parent="pim_catalog"
 * )
 */
class ChannelController extends Controller
{
    /**
     * List channels
     *
     * @param Request $request
     *
     * @Template
     * @Acl(
     *      id="pim_catalog_channel_index",
     *      name="View channel list",
     *      description="View channel list",
     *      parent="pim_catalog_channel"
     * )
     * @return Response
     */
    public function indexAction(Request $request)
    {
        /** @var $queryBuilder QueryBuilder */
        $queryBuilder = $this->getManager()->createQueryBuilder();
        $queryBuilder
            ->select('c')
            ->from('PimCatalogBundle:Channel', 'c');

        /** @var $queryFactory QueryFactory */
        $queryFactory = $this->get('pim_catalog.datagrid.manager.channel.default_query_factory');
        $queryFactory->setQueryBuilder($queryBuilder);

        /** @var $datagridManager LocaleDatagridManager */
        $datagridManager = $this->get('pim_catalog.datagrid.manager.channel');
        $datagrid = $datagridManager->getDatagrid();

        $view = ('json' === $request->getRequestFormat()) ?
            'OroGridBundle:Datagrid:list.json.php' : 'PimCatalogBundle:Channel:index.html.twig';

        return $this->render($view, array('datagrid' => $datagrid->createView()));
    }

    /**
     * Create channel
     *
     * @Template("PimCatalogBundle:Channel:edit.html.twig")
     * @Acl(
     *      id="pim_catalog_channel_create",
     *      name="Create a channel",
     *      description="Create a channel",
     *      parent="pim_catalog_channel"
     * )
     * @return array
     */
    public function createAction()
    {
        $channel = new Channel();

        return $this->editAction($channel);
    }

    /**
     * Edit channel
     *
     * @param Channel $channel
     *
     * @Template
     * @Acl(
     *      id="pim_catalog_channel_edit",
     *      name="Edit a channel",
     *      description="Edit a channel",
     *      parent="pim_catalog_channel"
     * )
     * @return array
     */
    public function editAction(Channel $channel)
    {
        if ($this->get('pim_catalog.form.handler.channel')->process($channel)) {
            $this->addFlash('success', 'Channel successfully saved');

            return $this->redirect(
                $this->generateUrl('pim_catalog_channel_index')
            );
        }

        return array(
            'form' => $this->get('pim_catalog.form.channel')->createView()
        );
    }

    /**
     * Remove channel
     *
     * @param Request $request
     * @param Channel $channel
     * @Acl(
     *      id="pim_catalog_channel_remove",
     *      name="Remove a channel",
     *      description="Remove a channel",
     *      parent="pim_catalog_channel"
     * )
     * @return Response
     */
    public function removeAction(Request $request, Channel $channel)
    {
        $this->remove($channel);

        if ($request->isXmlHttpRequest()) {
            return new Response('', 204);
        } else {
            $this->addFlash('success', 'Channel successfully removed');

            return $this->redirect($this->generateUrl('pim_catalog_channel_index'));
        }
    }
}
