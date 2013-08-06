<?php

namespace Pim\Bundle\ConfigBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Pim\Bundle\ConfigBundle\Controller\Controller;
use Pim\Bundle\ConfigBundle\Entity\Channel;

/**
 * Channel controller
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @Route("/channel")
 */
class ChannelController extends Controller
{
    /**
     * List channels
     *
     * @param Request $request
     *
     * @Route(
     *     "/.{_format}",
     *     requirements={"_format"="html|json"},
     *     defaults={"_format" = "html"}
     * )
     * @Template()
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        /** @var $queryBuilder QueryBuilder */
        $queryBuilder = $this->getManager()->createQueryBuilder();
        $queryBuilder
            ->select('c')
            ->from('PimConfigBundle:Channel', 'c');

        /** @var $queryFactory QueryFactory */
        $queryFactory = $this->get('pim_config.datagrid.manager.channel.default_query_factory');
        $queryFactory->setQueryBuilder($queryBuilder);

        /** @var $datagridManager LocaleDatagridManager */
        $datagridManager = $this->get('pim_config.datagrid.manager.channel');
        $datagrid = $datagridManager->getDatagrid();

        $view = ('json' === $request->getRequestFormat()) ?
            'OroGridBundle:Datagrid:list.json.php' : 'PimConfigBundle:Channel:index.html.twig';

        return $this->render($view, array('datagrid' => $datagrid->createView()));
    }

    /**
     * Create channel
     *
     * @Route("/create")
     * @Template("PimConfigBundle:Channel:edit.html.twig")
     *
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
     * @Route("/edit/{id}", requirements={"id"="\d+"}, defaults={"id"=0})
     * @Template
     *
     * @return array
     */
    public function editAction(Channel $channel)
    {
        if ($this->get('pim_config.form.handler.channel')->process($channel)) {
            $this->addFlash('success', 'Channel successfully saved');

            return $this->redirect(
                $this->generateUrl('pim_config_channel_index')
            );
        }

        return array(
            'form' => $this->get('pim_config.form.channel')->createView()
        );
    }

    /**
     * Remove channel
     *
     * @param Channel $channel
     *
     * @Route("/remove/{id}", requirements={"id"="\d+"})
     * @Method("DELETE")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function removeAction(Channel $channel)
    {
        $this->remove($channel);

        if ($this->getRequest()->isXmlHttpRequest()) {
            return new Response('', 204);
        } else {
            $this->addFlash('success', 'Channel successfully removed');

            return $this->redirect($this->generateUrl('pim_config_channel_index'));
        }
    }
}
