<?php

namespace Pim\Bundle\CatalogBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Pim\Bundle\CatalogBundle\Entity\Channel;

/**
 * Channel controller
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ChannelController extends Controller
{
    /**
     * List channels
     *
     * @param Request $request
     *
     * @Template
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
        $queryFactory = $this->get('pim_product.datagrid.manager.channel.default_query_factory');
        $queryFactory->setQueryBuilder($queryBuilder);

        /** @var $datagridManager LocaleDatagridManager */
        $datagridManager = $this->get('pim_product.datagrid.manager.channel');
        $datagrid = $datagridManager->getDatagrid();

        $view = ('json' === $request->getRequestFormat()) ?
            'OroGridBundle:Datagrid:list.json.php' : 'PimCatalogBundle:Channel:index.html.twig';

        return $this->render($view, array('datagrid' => $datagrid->createView()));
    }

    /**
     * Create channel
     *
     * @Template("PimCatalogBundle:Channel:edit.html.twig")
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
     * @return array
     */
    public function editAction(Channel $channel)
    {
        if ($this->get('pim_product.form.handler.channel')->process($channel)) {
            $this->addFlash('success', 'Channel successfully saved');

            return $this->redirect(
                $this->generateUrl('pim_product_channel_index')
            );
        }

        return array(
            'form' => $this->get('pim_product.form.channel')->createView()
        );
    }

    /**
     * Remove channel
     *
     * @param Request $request
     * @param Channel $channel
     *
     * @return Response
     */
    public function removeAction(Request $request, Channel $channel)
    {
        $this->remove($channel);

        if ($request->isXmlHttpRequest()) {
            return new Response('', 204);
        } else {
            $this->addFlash('success', 'Channel successfully removed');

            return $this->redirect($this->generateUrl('pim_product_channel_index'));
        }
    }
}
