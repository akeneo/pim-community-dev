<?php

namespace Pim\Bundle\ConfigBundle\Controller;

use Pim\Bundle\ConfigBundle\Entity\Channel;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

/**
 * Channel controller
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
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
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
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
     * Get entity manager
     *
     * @return \Doctrine\ORM\EntityManager
     */
    protected function getEntityManager()
    {
        return $this->getDoctrine()->getEntityManager();
    }

    /**
     * Get channel repository
     *
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getChannelRepository()
    {
        return $this->getEntityManager()->getRepository('PimConfigBundle:Channel');
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
            $this->get('session')->getFlashBag()->add('success', 'Channel successfully saved');

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
        $this->getEntityManager()->remove($channel);
        $this->getEntityManager()->flush();

        if ($this->getRequest()->isXmlHttpRequest()) {
            return new Response('', 204);
        } else {
            $this->get('session')->getFlashBag()->add('success', 'Channel successfully removed');

            return $this->redirect($this->generateUrl('pim_config_channel_index'));
        }
    }
}
