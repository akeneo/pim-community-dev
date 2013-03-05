<?php

namespace Pim\Bundle\ConfigBundle\Controller;

use Pim\Bundle\ConfigBundle\Entity\Channel;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

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
     * @return multitype
     *
     * @Route("/index")
     * @Template()
     */
    public function indexAction()
    {
        $channels = $this->getChannelRepository()->findAll();

        return array('channels' => $channels);
    }

    /**
     * Get storage manager
     *
     * @return \Doctrine\Common\Persistence\ObjectManager
     */
    protected function getStorageManager()
    {
        return $this->container->get('doctrine.orm.entity_manager');
    }

    /**
     * Get Channel Repository
     *
     * @return \Doctrine\Common\Persistence\ObjectRepository
     */
    protected function getChannelRepository()
    {
        return $this->getStorageManager()->getRepository('PimConfigBundle:Channel');
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
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function removeAction(Channel $channel)
    {
        $this->getStorageManager()->remove($channel);
        $this->getStorageManager()->flush();

        $this->get('session')->getFlashBag()->add('success', 'Channel successfully removed');

        return $this->redirect($this->generateUrl('pim_config_channel_index'));
    }
}
